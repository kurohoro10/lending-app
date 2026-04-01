<?php

/**
 * @file    app/Http/Controllers/Admin/Communication/EmailCommunicationController.php
 * @package App\Http\Controllers\Admin\Communication
 *
 * Handles all email communication operations for the Admin panel
 * within the commercial loan application system.
 *
 * Responsibilities:
 *  - Retrieving available email templates per application context
 *  - Dispatching outbound custom emails to clients
 *  - Ingesting and logging inbound webhook emails (Mailgun / SendGrid)
 *  - Listing, polling, and marking email threads for a given application
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Notifications\Admin\CustomEmail;
use App\Services\Communication\CommunicationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailCommunicationController extends Controller
{
    // =========================================================================
    // Template Retrieval
    // =========================================================================

    /**
     * Retrieve available email templates for the given application context.
     *
     * Returns a list of pre-configured email templates that are relevant
     * to the current state or type of the loan application.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               JSON payload containing the templates list.
     *
     * @response 200 {
     *   "success": true,
     *   "templates": [{ "id": 1, "name": "Welcome Email", ... }]
     * }
     */
    public function getTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getEmailTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    // =========================================================================
    // Outbound Email
    // =========================================================================

    /**
     * Dispatch a custom email to the client and log the outbound communication.
     *
     * Validates the request, fires a notification to the application's owner,
     * persists a Communication record for audit purposes, and logs the activity.
     *
     * @param  Request      $request      Incoming HTTP request containing subject and message.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success or error response.
     *
     * @bodyParam string subject required  Email subject line (max 255 chars).
     * @bodyParam string message required  Email body content (max 5000 chars).
     *
     * @response 200 { "success": true, "message": "Email sent successfully to client@example.com" }
     * @response 500 { "success": false, "message": "Failed to send email. Please check logs for details." }
     */
    public function send(Request $request, Application $application): JsonResponse
    {
        $validated = $this->validateOutboundEmail($request);

        try {
            $this->dispatchEmailNotification($application, $validated);
            $this->logOutboundCommunication($request, $application, $validated);

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

    // =========================================================================
    // Inbound Email (Webhook)
    // =========================================================================

    /**
     * Log an incoming email reply from the client.
     *
     * Intended to be called from an inbound email webhook (e.g. Mailgun inbound
     * parse, SendGrid inbound parse) or manually by an admin recording a reply.
     * Extracts the application number from the reply-to address, identifies the
     * sender, strips quoted reply history, and persists a Communication record.
     *
     * Expected payload fields:
     * - `from`           — Sender address (e.g. "John Doe <john@example.com>")
     * - `to`             — Recipient address containing the application token
     *                      (e.g. "reply-APP-2026-000001@mail.example.com")
     * - `subject`        — Email subject line
     * - `stripped-text`  — Pre-stripped plain-text body (Mailgun)
     * - `text`           — Plain-text body fallback
     * - `email`          — Raw MIME message fallback for manual parsing
     *
     * @param  Request  $request  The incoming webhook HTTP request.
     * @return JsonResponse       Success payload with communication ID, or error details.
     *
     * @response 200 { "success": true, "communication_id": 42 }
     * @response 422 { "success": false, "message": "Could not identify application." }
     * @response 404 { "success": false, "message": "Application not found." }
     * @response 500 { "success": false, "message": "Failed to log inbound email." }
     */
    public function incoming(Request $request): JsonResponse
    {
        $from    = $request->input('from');
        $to      = $request->input('to');
        $subject = $request->input('subject');

        $body = $this->extractEmailBody($request);

        if ($body) {
            $body = self::stripQuotedReply($body);
        }

        $body      = $body ?: null;
        $fromEmail = $this->extractEmailAddress($from);
        $applicationNumber = $this->extractApplicationNumber($to);

        if (! $applicationNumber) {
            Log::warning('Inbound email: could not extract application number.', ['to' => $to]);
            return response()->json(['success' => false, 'message' => 'Could not identify application.'], 422);
        }

        if (! $fromEmail) {
            Log::warning('Inbound email: could not extract sender email.', ['from' => $from]);
            return response()->json(['success' => false, 'message' => 'Could not identify sender.'], 422);
        }

        if (! $body) {
            Log::warning('Inbound email: empty body after stripping.', ['from' => $fromEmail]);
            return response()->json(['success' => false, 'message' => 'Email body is empty.'], 422);
        }

        $application = Application::where('application_number', $applicationNumber)->first();

        if (! $application) {
            Log::warning('Inbound email: no application found.', ['application_number' => $applicationNumber]);
            return response()->json(['success' => false, 'message' => 'Application not found.'], 404);
        }

        try {
            $communication = $this->logInboundCommunication($request, $application, $fromEmail, $subject, $body);

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

    // =========================================================================
    // Listing & Polling
    // =========================================================================

    /**
     * List all email communications for a given application (admin view).
     *
     * Returns both inbound and outbound email records in ascending chronological
     * order, along with the name of the admin who sent each outbound message.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               JSON payload with an `emails` array.
     *
     * @response 200 {
     *   "success": true,
     *   "emails": [{ "id": 1, "direction": "outbound", "subject": "...", ... }]
     * }
     */
    public function index(Application $application): JsonResponse
    {
        $emails = $application->communications()
            ->with('user')
            ->emails()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($c) => $this->formatCommunicationForIndex($c));

        return response()->json([
            'success' => true,
            'emails'  => $emails,
        ]);
    }

    /**
     * Return new inbound emails since a given timestamp (AJAX polling).
     *
     * Intended for front-end polling loops. Accepts an optional `after` query
     * parameter (ISO 8601 / MySQL datetime string) and returns only inbound
     * messages created after that point, along with global unread counts for
     * both email and SMS channels.
     *
     * @param  Request      $request      HTTP request; may include `?after=` query param.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               New messages and unread badge counts.
     *
     * @queryParam string after  Optional ISO datetime to fetch messages created after.
     *
     * @response 200 {
     *   "success": true,
     *   "messages": [...],
     *   "unread_email": 3,
     *   "unread_sms": 1
     * }
     * @response 500 { "success": false, "messages": [], "unread_email": 0, "unread_sms": 0 }
     */
    public function poll(Request $request, Application $application): JsonResponse
    {
        try {
            $after = $request->query('after');

            $emails = $this->fetchInboundEmailsSince($application, $after);

            $unreadEmail = $this->countUnread($application, 'emails');
            $unreadSms   = $this->countUnread($application, 'sms');

            return response()->json([
                'success'      => true,
                'messages'     => $emails,
                'unread_email' => $unreadEmail,
                'unread_sms'   => $unreadSms,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to poll email communications', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success'      => false,
                'messages'     => [],
                'unread_email' => 0,
                'unread_sms'   => 0,
                'message'      => 'Failed to fetch new messages.',
            ], 500);
        }
    }

    // =========================================================================
    // Read Status
    // =========================================================================

    /**
     * Mark an inbound email communication as read.
     *
     * Aborts with HTTP 403 if the communication is not of type `email_in`,
     * preventing outbound records from being marked erroneously.
     *
     * @param  Communication  $communication  The bound communication model instance.
     * @return JsonResponse                   Simple success acknowledgement.
     *
     * @response 200 { "success": true }
     * @response 403 Forbidden — only inbound emails can be marked as read.
     */
    public function markRead(Communication $communication): JsonResponse
    {
        abort_if(
            $communication->type !== 'email_in',
            403,
            'Only inbound emails can be marked as read.'
        );

        $communication->markAsRead();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Private Helpers — Outbound
    // =========================================================================

    /**
     * Validate the outbound email request fields.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `subject` and `message` fields.
     */
    private function validateOutboundEmail(Request $request): array
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);
    }

    /**
     * Fire the CustomEmail notification to the application's user.
     *
     * @param  Application  $application  The target application.
     * @param  array        $validated    Validated subject and message payload.
     * @return void
     */
    private function dispatchEmailNotification(Application $application, array $validated): void
    {
        $application->user->notify(
            new CustomEmail(
                $application,
                $validated['subject'],
                $validated['message']
            )
        );
    }

    /**
     * Persist a Communication record for an outbound email.
     *
     * @param  Request      $request      HTTP request (used for sender IP).
     * @param  Application  $application  The associated application.
     * @param  array        $validated    Validated subject and message payload.
     * @return void
     */
    private function logOutboundCommunication(Request $request, Application $application, array $validated): void
    {
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
    }

    // =========================================================================
    // Private Helpers — Inbound Parsing
    // =========================================================================

    /**
     * Extract the plain-text body from an inbound webhook request.
     *
     * Checks provider-specific fields first (`stripped-text`, `text`),
     * then falls back to parsing the raw MIME payload in the `email` field.
     *
     * @param  Request  $request  The inbound webhook request.
     * @return string|null        Extracted plain-text body, or null if unavailable.
     */
    private function extractEmailBody(Request $request): ?string
    {
        $body = $request->input('stripped-text')
             ?? $request->input('text')
             ?? null;

        if (! $body && $request->input('email')) {
            $body = $this->parseRawMimeBody($request->input('email'));
        }

        return $body ?: null;
    }

    /**
     * Attempt to extract a plain-text body from a raw MIME email string.
     *
     * First tries the `text/plain` part; falls back to stripping tags from
     * the `text/html` part if plain text is absent.
     *
     * @param  string  $rawEmail  The full raw MIME message string.
     * @return string|null        Decoded body text, or null if unparseable.
     */
    private function parseRawMimeBody(string $rawEmail): ?string
    {
        // Prefer text/plain
        if (preg_match('/Content-Type: text\/plain.*?\r\n\r\n(.*?)(\r\n--|\z)/s', $rawEmail, $matches)) {
            return trim(quoted_printable_decode($matches[1]));
        }

        // Fall back to text/html
        if (preg_match('/Content-Type: text\/html.*?\r\n\r\n(.*?)(\r\n--|\z)/s', $rawEmail, $matches)) {
            return trim(strip_tags(quoted_printable_decode($matches[1])));
        }

        return null;
    }

    /**
     * Extract a bare email address from a display-name formatted string.
     *
     * Handles formats such as:
     *  - `"John Doe <john@example.com>"`
     *  - `"john@example.com"`
     *
     * @param  string|null  $from  Raw `From` header value.
     * @return string|null         Bare email address, or null if extraction fails.
     */
    private function extractEmailAddress(?string $from): ?string
    {
        preg_match('/[\w.+\-]+@[\w\-]+\.[\w.]+/', $from ?? '', $matches);

        return $matches[0] ?? null;
    }

    /**
     * Extract the application number encoded in the reply-to address.
     *
     * Expects the format: `reply-{APPLICATION_NUMBER}@domain.tld`
     *
     * @param  string|null  $to  Raw `To` header value from the inbound webhook.
     * @return string|null       The extracted application number, or null if not found.
     */
    private function extractApplicationNumber(?string $to): ?string
    {
        if (preg_match('/reply-([^@]+)@/', $to ?? '', $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Persist a Communication record for an inbound email.
     *
     * @param  Request      $request      HTTP request (used for sender IP).
     * @param  Application  $application  The matched application.
     * @param  string       $fromEmail    The extracted sender address.
     * @param  string|null  $subject      The email subject line.
     * @param  string       $body         The stripped plain-text body.
     * @return Communication              The newly created communication record.
     */
    private function logInboundCommunication(
        Request $request,
        Application $application,
        string $fromEmail,
        ?string $subject,
        string $body
    ): Communication {
        return Communication::create([
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
    }

    /**
     * Strip quoted reply history from an email body string.
     *
     * Removes common reply indicators to retain only the new content:
     *  - Lines beginning with `>`
     *  - Separator lines (`---`, `___`, etc.)
     *  - "On [date] ... wrote:" attribution blocks (including multi-line variants)
     *
     * @param  string  $text  Raw email body text.
     * @return string         Cleaned body containing only the new reply content.
     */
    private static function stripQuotedReply(string $text): string
    {
        $text  = str_replace("\r\n", "\n", $text);
        $lines = explode("\n", $text);
        $output = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line    = $lines[$i];
            $trimmed = trim($line);

            // Stop at any quoted line
            if (str_starts_with($trimmed, '>')) {
                break;
            }

            // Stop at common horizontal separators (---, ___, etc.)
            if (preg_match('/^[-_]{3,}$/', $trimmed)) {
                break;
            }

            // Build a lookahead spanning up to 3 lines to catch multi-line attributions
            // e.g. "On Mon, 19 Mar 2026, John Doe\n<john@example.com> wrote:"
            $lookahead = $trimmed;
            if (isset($lines[$i + 1])) {
                $lookahead .= ' ' . trim($lines[$i + 1]);
            }
            if (isset($lines[$i + 2])) {
                $lookahead .= ' ' . trim($lines[$i + 2]);
            }

            if (preg_match('/^On .{5,}wrote:\s*$/s', $lookahead)) {
                break;
            }

            // Catch trailing "wrote:" line and remove its preceding "On ..." anchor
            if (preg_match('/wrote:\s*$/', $trimmed) && $i > 0) {
                while (! empty($output) && preg_match('/^On\s/i', trim(end($output)))) {
                    array_pop($output);
                }
                break;
            }

            $output[] = $line;
        }

        return trim(implode("\n", $output));
    }

    // =========================================================================
    // Private Helpers — Listing & Polling
    // =========================================================================

    /**
     * Map a Communication model to the array shape used by the index endpoint.
     *
     * @param  Communication  $c  The communication record to format.
     * @return array              Associative array suitable for JSON serialisation.
     */
    private function formatCommunicationForIndex(Communication $c): array
    {
        return [
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
        ];
    }

    /**
     * Retrieve inbound email communications created after an optional timestamp.
     *
     * @param  Application  $application  The target application.
     * @param  string|null  $after        ISO / MySQL datetime string, or null for all.
     * @return \Illuminate\Support\Collection  Collection of formatted email arrays.
     */
    private function fetchInboundEmailsSince(Application $application, ?string $after): \Illuminate\Support\Collection
    {
        $query = $application->communications()
            ->emails()
            ->where('direction', 'inbound')
            ->orderBy('created_at', 'asc');

        if ($after) {
            $query->where('created_at', '>', $after);
        }

        return $query->get()->map(fn ($c) => [
            'id'         => $c->id,
            'direction'  => $c->direction,
            'from'       => $c->from_address,
            'subject'    => $c->subject,
            'body'       => $c->body,
            'status'     => $c->status,
            'sent_by'    => $c->user?->name,
            'created_at' => $c->created_at->toDateTimeString(),
            'formatted'  => $c->created_at->format('d M Y, g:ia'),
        ]);
    }

    /**
     * Count unread inbound communications for a given channel on an application.
     *
     * @param  Application  $application  The target application.
     * @param  string       $channel      Scope method name: `'emails'` or `'sms'`.
     * @return int                        Number of unread inbound records.
     */
    private function countUnread(Application $application, string $channel): int
    {
        return $application->communications()
            ->{$channel}()
            ->whereNull('read_at')
            ->where('direction', 'inbound')
            ->count();
    }
}