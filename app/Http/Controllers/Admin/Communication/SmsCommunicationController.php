<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Services\Communication\CommunicationTemplateService;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsCommunicationController extends Controller
{
    /**
     * Retrieve available SMS templates for the given application context.
     */
    public function getTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getSMSTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Dispatch an SMS to the client via Twilio and log the activity.
     */
    public function send(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        if (!$application->personalDetails?->mobile_phone) {
            return response()->json([
                'success' => false,
                'message' => 'No phone number available for this client.',
            ], 400);
        }

        try {
            app(MessagingService::class)->send(
                $application->personalDetails->mobile_phone,
                $validated['message'],
                $application
            );

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

    /**
     * Handle an incoming SMS from Twilio webhook.
     *
     * Twilio POST fields (form-encoded):
     *   MessageSid   — unique message ID
     *   From         — sender phone number (E.164)
     *   To           — your Twilio number
     *   Body         — message text
     *   NumMedia     — number of media attachments
     *
     * Twilio validates via signature — add VerifyTwilioRequest middleware on the route.
     * The application is resolved by matching the From number to a personalDetails record.
     */
    public function incoming(Request $request): \Illuminate\Http\Response
    {
        try {
            app(\App\Services\TwilioService::class)->handleIncoming($request->all());
        } catch (\Exception $e) {
            Log::error('Failed to handle inbound SMS webhook', ['error' => $e->getMessage()]);
        }

        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Handle Twilio delivery status webhook (StatusCallback).
     *
     * Twilio POST fields:
     *   MessageSid      — the original message SID
     *   MessageStatus   — delivered | sent | failed | undelivered
     *   ErrorCode       — optional Twilio error code on failure
     */
    public function deliveryStatus(Request $request): \Illuminate\Http\Response
    {
        try {
            $messageSid = $request->input('MessageSid');
            $status     = $request->input('MessageStatus');

            if ($messageSid && $status) {
                app(\App\Services\TwilioService::class)->updateStatus($messageSid, $status);
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle SMS status webhook', ['error' => $e->getMessage()]);
        }

        return response('', 204);
    }

    /**
     * List SMS communications for a given application (admin view).
     */
    public function index(Application $application): JsonResponse
    {
        $messages = $application->communications()
            ->with('user')
            ->sms()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($c) => [
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
            ]);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Mark an inbound SMS as read.
     */
    public function markRead(Communication $communication): JsonResponse
    {
        abort_if($communication->type !== 'sms_in', 403, 'Only inbound SMS can be marked as read.');

        $communication->markAsRead();

        return response()->json(['success' => true]);
    }
}
