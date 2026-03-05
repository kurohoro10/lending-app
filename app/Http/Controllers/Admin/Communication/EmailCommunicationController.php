<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Services\Communication\CommunicationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailCommunicationController extends Controller
{
    /**
     * Retrieve available email templates for the given application context.
     */
    public function getTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getEmailTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Dispatch a custom email to the client and log the outbound communication.
     */
    public function send(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $application->user->notify(
                new \App\Notifications\Admin\CustomEmail(
                    $application,
                    $validated['subject'],
                    $validated['message']
                )
            );

            Communication::create([
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'type'           => 'email_out',
                'direction'      => 'outbound',
                'from_address'   => config('mail.from.address'),
                'to_address'     => $application->user->email,
                'subject'        => $validated['subject'],
                'body'           => $validated['message'],
                'status'         => 'sent',
                'sent_at'        => now(),
                'sender_ip'      => $request->ip(),
            ]);

            ActivityLog::logActivity('email_sent', 'Email sent to client', $application);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $application->user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check logs for details.',
            ], 500);
        }
    }

    /**
     * Log an incoming email reply from the client.
     *
     * Called from a webhook (e.g. Mailgun, SendGrid inbound parse)
     * or manually by an admin recording a received reply.
     *
     * Expected payload:
     *   application_id  — required
     *   from            — sender email address
     *   subject         — email subject
     *   body            — plain-text body
     *   received_at     — optional ISO timestamp (defaults to now)
     */
    public function incoming(Request $request): JsonResponse
    {
        $from    = $request->input('from');
        $to      = $request->input('to');
        $subject = $request->input('subject');
        $body    = $request->input('stripped-text')
                ?? $request->input('text')
                ?? null;

        // Fallback: parse raw MIME
        if (!$body && $request->input('email')) {
            $rawEmail = $request->input('email');
            if (preg_match('/Content-Type: text\/plain.*?\r\n\r\n(.*?)(\r\n--|\z)/s', $rawEmail, $mimeMatches)) {
                $body = trim(quoted_printable_decode($mimeMatches[1]));
            }
            if (!$body && preg_match('/Content-Type: text\/html.*?\r\n\r\n(.*?)(\r\n--|\z)/s', $rawEmail, $mimeMatches)) {
                $body = trim(strip_tags(quoted_printable_decode($mimeMatches[1])));
            }
        }

        // Strip quoted reply history
        if ($body) {
            $body = self::stripQuotedReply($body);
        }

        $body = $body ?: null;

        // Extract clean email
        preg_match('/[\w.+\-]+@[\w\-]+\.[\w.]+/', $from ?? '', $fromMatches);
        $fromEmail = $fromMatches[0] ?? null;

        // Extract application number from reply-APP-2026-000001@...
        $applicationNumber = null;
        if (preg_match('/reply-([^@]+)@/', $to ?? '', $appMatches)) {
            $applicationNumber = $appMatches[1];
        }

        if (!$applicationNumber) {
            Log::warning('Inbound email: could not extract application number.', ['to' => $to]);
            return response()->json(['success' => false, 'message' => 'Could not identify application.'], 422);
        }

        if (!$fromEmail) {
            Log::warning('Inbound email: could not extract sender email.', ['from' => $from]);
            return response()->json(['success' => false, 'message' => 'Could not identify sender.'], 422);
        }

        if (!$body) {
            Log::warning('Inbound email: empty body after stripping.', ['from' => $fromEmail]);
            return response()->json(['success' => false, 'message' => 'Email body is empty.'], 422);
        }

        $application = Application::where('application_number', $applicationNumber)->first();

        if (!$application) {
            Log::warning('Inbound email: no application found.', ['application_number' => $applicationNumber]);
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }

        try {
            $communication = Communication::create([
                'application_id' => $application->id,
                'user_id'        => $application->user_id,
                'type'           => 'email_in',
                'direction'      => 'inbound',
                'from_address'   => $fromEmail,
                'to_address'     => config('mail.from.address'),
                'subject'        => $subject,
                'body'           => $body,
                'status'         => 'delivered',
                'delivered_at'   => now(),
                'sender_ip'      => $request->ip(),
            ]);

            ActivityLog::logActivity('email_received', 'Inbound email received from client', $application);

            Log::info('Inbound email logged', [
                'application_id'   => $application->id,
                'communication_id' => $communication->id,
                'from'             => $fromEmail,
            ]);

            return response()->json(['success' => true, 'communication_id' => $communication->id]);

        } catch (\Exception $e) {
            Log::error('Failed to log inbound email', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to log inbound email.'], 500);
        }
    }

    private static function stripQuotedReply(string $text): string
    {
        // Normalize line endings
        $text  = str_replace("\r\n", "\n", $text);
        $lines = explode("\n", $text);
        $output = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line    = $lines[$i];
            $trimmed = trim($line);

            // Stop at any line starting with >
            if (str_starts_with($trimmed, '>')) {
                break;
            }

            // Stop at common separators
            if (preg_match('/^[-_]{3,}$/', $trimmed)) {
                break;
            }

            // Stop at "On [anything] wrote:" — may span up to 3 lines
            // Look ahead: join current + next 2 lines and check
            $lookahead = $trimmed;
            if (isset($lines[$i + 1])) $lookahead .= ' ' . trim($lines[$i + 1]);
            if (isset($lines[$i + 2])) $lookahead .= ' ' . trim($lines[$i + 2]);

            if (preg_match('/^On .{5,}wrote:\s*$/s', $lookahead)) {
                break;
            }

            // Also catch the second line of "On ... wrote:" mid-pattern
            // e.g. line is just "> tarkov BAK <tarkovbak@gmail.com> wrote:"
            if (preg_match('/wrote:\s*$/', $trimmed) && $i > 0) {
                // Remove the previous "On ..." line we already added
                while (!empty($output) && preg_match('/^On\s/i', trim(end($output)))) {
                    array_pop($output);
                }
                break;
            }

            $output[] = $line;
        }

        return trim(implode("\n", $output));
    }

    /**
     * List email communications for a given application (admin view).
     */
    public function index(Application $application): JsonResponse
    {
        $emails = $application->communications()
            ->with('user')
            ->emails()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($c) => [
                'id'           => $c->id,
                'direction'    => $c->direction,
                'from_address' => $c->from_address,
                'to_address'   => $c->to_address,
                'subject'      => $c->subject,
                'body'         => $c->body,
                'status'       => $c->status,
                'sent_by'      => $c->user?->name,
                'sent_at'      => $c->sent_at?->toDateTimeString(),
                'delivered_at' => $c->delivered_at?->toDateTimeString(),
                'created_at'   => $c->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'success' => true,
            'emails'  => $emails,
        ]);
    }

    /**
     * Mark an inbound email as read.
     */
    public function markRead(Communication $communication): JsonResponse
    {
        abort_if($communication->type !== 'email_in', 403, 'Only inbound emails can be marked as read.');

        $communication->markAsRead();

        return response()->json(['success' => true]);
    }
}
