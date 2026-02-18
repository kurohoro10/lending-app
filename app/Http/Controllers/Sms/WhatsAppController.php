<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Services\MessagingService; // ✅ FIX: Use MessagingService instead of TwilioService directly
use App\Jobs\SendWhatsAppMessage;
use App\Jobs\SendSMSMessage;
use App\Models\Application;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Send message synchronously (uses MessagingService to decide WhatsApp vs SMS)
     */
    public function send(Request $request, MessagingService $messaging)
    {
        $validated = $request->validate([
            'phone'          => ['required', 'string'],
            'message'        => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        try {
            $application = isset($validated['application_id'])
                ? Application::find($validated['application_id'])
                : null;

            // ✅ FIX: Use MessagingService — when you go live, change the one
            //         line in MessagingService::send() and this just works.
            $messaging->send($validated['phone'], $validated['message'], $application);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ✅ FIX: REMOVED the dead `sendSMS()` protected method that was here.
    //
    // It was never called from this class, had a missing Log import,
    // and duplicated logic already in ApplicationController::sendApplicationSMS().

    /**
     * Queue WhatsApp message (asynchronous)
     */
    public function queue(Request $request)
    {
        $validated = $request->validate([
            'phone'          => ['required', 'string'],
            'message'        => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        SendWhatsAppMessage::dispatch(
            $validated['phone'],
            $validated['message'],
            $validated['application_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp message queued for sending.',
        ]);
    }

    /**
     * Queue SMS message (asynchronous)
     */
    public function queueSMS(Request $request)
    {
        $validated = $request->validate([
            'phone'          => ['required', 'string'],
            'message'        => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        SendSMSMessage::dispatch(
            $validated['phone'],
            $validated['message'],
            $validated['application_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'SMS queued for sending.',
        ]);
    }
}
