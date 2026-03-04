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
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'from'           => 'required|email|max:255',
            'subject'        => 'nullable|string|max:255',
            'body'           => 'required|string',
            'received_at'    => 'nullable|date',
        ]);

        try {
            $application = Application::findOrFail($validated['application_id']);

            $communication = Communication::create([
                'application_id' => $application->id,
                'user_id'        => $application->user_id,
                'type'           => 'email_in',
                'direction'      => 'inbound',
                'from_address'   => $validated['from'],
                'to_address'     => config('mail.from.address'),
                'subject'        => $validated['subject'] ?? null,
                'body'           => $validated['body'],
                'status'         => 'delivered',
                'delivered_at'   => $validated['received_at'] ?? now(),
                'sender_ip'      => $request->ip(),
            ]);

            ActivityLog::logActivity('email_received', 'Inbound email received from client', $application);

            Log::info('Inbound email logged', [
                'application_id'   => $application->id,
                'communication_id' => $communication->id,
                'from'             => $validated['from'],
            ]);

            return response()->json([
                'success'          => true,
                'message'          => 'Inbound email logged successfully.',
                'communication_id' => $communication->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log inbound email', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to log inbound email.',
            ], 500);
        }
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
