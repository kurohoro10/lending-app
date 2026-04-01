<?php

/**
 * @file    app/Http/Controllers/Admin/Communication/SmsCommunicationController.php
 * @package App\Http\Controllers\Admin\Communication
 *
 * Handles all SMS and WhatsApp communication operations for the Admin panel
 * within the commercial loan application system.
 *
 * Responsibilities:
 *  - Retrieving available SMS templates per application context
 *  - Dispatching outbound SMS messages to clients via Twilio / MessagingService
 *  - Ingesting inbound SMS messages from the Twilio webhook
 *  - Processing Twilio delivery status callbacks
 *  - Listing, polling, and marking SMS threads for a given application
 *
 * Route middleware note:
 *  - `incoming()`       — should be protected by VerifyTwilioRequest middleware
 *  - `deliveryStatus()` — should be protected by VerifyTwilioRequest middleware
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Services\Communication\CommunicationTemplateService;
use App\Services\MessagingService;
use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SmsCommunicationController extends Controller
{
    // =========================================================================
    // Template Retrieval
    // =========================================================================

    /**
     * Retrieve available SMS templates for the given application context.
     *
     * Returns a list of pre-configured SMS templates that are relevant
     * to the current state or type of the loan application.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               JSON payload containing the templates list.
     *
     * @response 200 {
     *   "success": true,
     *   "templates": [{ "id": 1, "name": "Approval Notification", ... }]
     * }
     */
    public function getTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getSMSTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    // =========================================================================
    // Outbound SMS
    // =========================================================================

    /**
     * Dispatch an SMS to the client via Twilio and log the activity.
     *
     * Validates that the application's client has a mobile phone number on
     * record before attempting delivery. On success, logs the activity to the
     * audit trail. On failure, records the error and returns a 500 response.
     *
     * @param  Request      $request      Incoming HTTP request containing the message body.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success or error response.
     *
     * @bodyParam string message required  SMS body text (max 1000 chars).
     *
     * @response 200 { "success": true, "message": "SMS sent successfully to +61400000000" }
     * @response 400 { "success": false, "message": "No phone number available for this client." }
     * @response 500 { "success": false, "message": "Failed to send SMS. Service may be temporarily unavailable." }
     */
    public function send(Request $request, Application $application): JsonResponse
    {
        $validated = $this->validateOutboundSms($request);

        if (! $this->clientHasPhoneNumber($application)) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number available for this client.',
            ], 400);
        }

        try {
            $this->dispatchSmsNotification($application, $validated['message']);

            ActivityLog::logActivity('sms_sent', 'SMS sent to client', $application);

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully to ' . $application->personalDetails->mobile_phone,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS. Service may be temporarily unavailable.',
            ], 500);
        }
    }

    // =========================================================================
    // Inbound Webhooks (Twilio)
    // =========================================================================

    /**
     * Handle an incoming SMS from the Twilio inbound webhook.
     *
     * Delegates full processing to TwilioService and always returns an empty
     * TwiML response so Twilio does not retry. Errors are swallowed and logged
     * to prevent Twilio from treating a 5xx as a failed delivery.
     *
     * Expected Twilio POST fields (form-encoded):
     *  - `MessageSid`  — unique message identifier
     *  - `From`        — sender phone number in E.164 format
     *  - `To`          — your Twilio receiving number
     *  - `Body`        — SMS text content
     *  - `NumMedia`    — number of media attachments (0 for plain SMS)
     *
     * The application is resolved by matching `From` to a `personalDetails`
     * record inside TwilioService.
     *
     * @param  Request  $request  The Twilio webhook POST request.
     * @return Response           Empty TwiML XML response with HTTP 200.
     *
     * @response 200 <?xml version="1.0" encoding="UTF-8"?><Response></Response>
     */
    public function incoming(Request $request): Response
    {
        try {
            app(TwilioService::class)->handleIncoming($request->all());
        } catch (\Exception $e) {
            Log::error('Failed to handle inbound SMS webhook', [
                'error' => $e->getMessage(),
            ]);
        }

        return $this->emptyTwimlResponse();
    }

    /**
     * Handle a Twilio delivery status callback (StatusCallback URL).
     *
     * Updates the Communication record's status based on Twilio's report.
     * Always returns HTTP 204 — Twilio does not process the response body.
     * Errors are logged but do not affect the HTTP status code, as Twilio
     * would otherwise retry failed callbacks.
     *
     * Expected Twilio POST fields:
     *  - `MessageSid`    — the original outbound message SID
     *  - `MessageStatus` — one of: `queued` | `sent` | `delivered` | `failed` | `undelivered`
     *  - `ErrorCode`     — optional Twilio error code present on failure statuses
     *
     * @param  Request  $request  The Twilio status callback POST request.
     * @return Response           Empty HTTP 204 response.
     *
     * @response 204
     */
    public function deliveryStatus(Request $request): Response
    {
        try {
            $messageSid = $request->input('MessageSid');
            $status     = $request->input('MessageStatus');

            if ($messageSid && $status) {
                app(TwilioService::class)->updateStatus($messageSid, $status);
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle SMS status webhook', [
                'error' => $e->getMessage(),
            ]);
        }

        return response('', 204);
    }

    // =========================================================================
    // Listing & Polling
    // =========================================================================

    /**
     * List all SMS communications for a given application (admin view).
     *
     * Returns both inbound and outbound SMS records in ascending chronological
     * order, including the name of the admin who sent each outbound message
     * and any Twilio external message SID for reconciliation.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               JSON payload with a `messages` array.
     *
     * @response 200 {
     *   "success": true,
     *   "messages": [{ "id": 1, "direction": "outbound", "body": "...", ... }]
     * }
     */
    public function index(Application $application): JsonResponse
    {
        $messages = $application->communications()
            ->with('user')
            ->sms()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($c) => $this->formatCommunicationForIndex($c));

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Return new inbound SMS / WhatsApp messages since a given timestamp (AJAX polling).
     *
     * Accepts an optional `after` query parameter (ISO 8601 / MySQL datetime string)
     * and returns only inbound records created after that point. Also returns global
     * unread badge counts for both the email and SMS channels.
     *
     * @param  Request      $request      HTTP request; may include `?after=` query param.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               New messages and unread badge counts.
     *
     * @queryParam string after  Optional ISO datetime — fetch messages created after this point.
     *
     * @response 200 {
     *   "success": true,
     *   "messages": [...],
     *   "unread_email": 2,
     *   "unread_sms": 1
     * }
     * @response 500 { "success": false, "messages": [], "unread_email": 0, "unread_sms": 0 }
     */
    public function poll(Request $request, Application $application): JsonResponse
    {
        try {
            $after    = $request->query('after');
            $messages = $this->fetchInboundSmsSince($application, $after);

            $unreadEmail = $this->countUnread($application, 'emails');
            $unreadSms   = $this->countUnread($application, 'sms');

            return response()->json([
                'success'      => true,
                'messages'     => $messages,
                'unread_email' => $unreadEmail,
                'unread_sms'   => $unreadSms,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to poll SMS communications', [
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
     * Mark an inbound SMS communication as read.
     *
     * Aborts with HTTP 403 if the communication is not of type `sms_in`,
     * preventing outbound records from being marked erroneously.
     *
     * @param  Communication  $communication  The bound communication model instance.
     * @return JsonResponse                   Simple success acknowledgement.
     *
     * @response 200 { "success": true }
     * @response 403 Forbidden — only inbound SMS can be marked as read.
     */
    public function markRead(Communication $communication): JsonResponse
    {
        abort_if(
            $communication->type !== 'sms_in',
            403,
            'Only inbound SMS can be marked as read.'
        );

        $communication->markAsRead();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Private Helpers — Outbound
    // =========================================================================

    /**
     * Validate the outbound SMS request fields.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `message` field.
     */
    private function validateOutboundSms(Request $request): array
    {
        return $request->validate([
            'message' => 'required|string|max:1000',
        ]);
    }

    /**
     * Determine whether the application's client has a mobile phone number on record.
     *
     * @param  Application  $application  The target application.
     * @return bool                       True if a mobile phone number is present.
     */
    private function clientHasPhoneNumber(Application $application): bool
    {
        return ! empty($application->personalDetails?->mobile_phone);
    }

    /**
     * Dispatch the SMS via MessagingService.
     *
     * @param  Application  $application  The target application.
     * @param  string       $message      The SMS body text to send.
     * @return void
     */
    private function dispatchSmsNotification(Application $application, string $message): void
    {
        app(MessagingService::class)->send(
            $application->personalDetails->mobile_phone,
            $message,
            $application
        );
    }

    // =========================================================================
    // Private Helpers — Webhooks
    // =========================================================================

    /**
     * Build an empty TwiML XML response.
     *
     * Twilio requires a valid TwiML document in response to inbound webhooks.
     * An empty `<Response>` element instructs Twilio to take no further action.
     *
     * @return Response  HTTP 200 response with `Content-Type: text/xml`.
     */
    private function emptyTwimlResponse(): Response
    {
        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
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
            'type'         => $c->type,
            'from_address' => $c->from_address,
            'to_address'   => $c->to_address,
            'body'         => $c->body,
            'status'       => $c->status,
            'sent_by'      => $c->user?->name,
            'external_id'  => $c->external_id,
            'sent_at'      => $c->sent_at?->toDateTimeString(),
            'delivered_at' => $c->delivered_at?->toDateTimeString(),
            'created_at'   => $c->created_at->toDateTimeString(),
        ];
    }

    /**
     * Retrieve inbound SMS communications created after an optional timestamp.
     *
     * @param  Application  $application  The target application.
     * @param  string|null  $after        ISO / MySQL datetime string, or null for all.
     * @return Collection                 Collection of formatted SMS message arrays.
     */
    private function fetchInboundSmsSince(Application $application, ?string $after): Collection
    {
        $query = $application->communications()
            ->sms()
            ->where('direction', 'inbound')
            ->orderBy('created_at', 'asc');

        if ($after) {
            $query->where('created_at', '>', $after);
        }

        return $query->get()->map(fn ($c) => [
            'id'         => $c->id,
            'direction'  => $c->direction,
            'from'       => $c->from_address,
            'body'       => $c->body,
            'type'       => $c->type,
            'status'     => $c->status,
            'sent_by'    => $c->user?->name,
            'created_at' => $c->created_at->toDateTimeString(),
            'formatted'  => $c->created_at->format('d M, g:ia'),
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