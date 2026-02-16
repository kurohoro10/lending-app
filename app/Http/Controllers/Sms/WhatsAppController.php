<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Services\TwilioService;
use App\Jobs\SendWhatsAppMessage;
use App\Jobs\SendSMSMessage;
use App\Models\Application;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Send WhatsApp message (synchronous)
     */
    public function send(Request $request, TwilioService $twilio)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        try {
            $result = $twilio->sendWhatsApp(
                $validated['phone'],
                $validated['message'],
                isset($validated['application_id'])
                    ? \App\Models\Application::find($validated['application_id'])
                    : null
            );

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send SMS message (synchronous)
     */
    protected function sendSMS(Application $application, string $message): void
    {
        Log::info('sendSMS called', [
            'application_id' => $application->id,
            'has_personal_details' => $application->personalDetails !== null,
            'mobile_phone' => $application->personalDetails?->mobile_phone ?? 'NO PHONE',
        ]);

        if (!$application->personalDetails) {
            Log::warning('No personal details found for application', [
                'application_id' => $application->id
            ]);
            return;
        }

        if (!$application->personalDetails->mobile_phone) {
            Log::warning('No mobile phone found', [
                'application_id' => $application->id,
                'personal_details_id' => $application->personalDetails->id,
            ]);
            return;
        }

        Log::info('Dispatching SMS job', [
            'phone' => $application->personalDetails->mobile_phone,
            'message' => $message,
            'application_id' => $application->id,
        ]);

        try {
            SendSMSMessage::dispatch(
                $application->personalDetails->mobile_phone,
                $message,
                $application->id
            );
            Log::info('SMS job dispatched successfully');
        } catch (\Exception $e) {
            Log::error('Failed to dispatch SMS job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Queue WhatsApp message (asynchronous)
     */
    public function queue(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        SendWhatsAppMessage::dispatch(
            $validated['phone'],
            $validated['message'],
            $validated['application_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp message queued for sending.'
        ]);
    }

    /**
     * Queue SMS message (asynchronous)
     */
    public function queueSMS(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1000'],
            'application_id' => ['nullable', 'exists:applications,id'],
        ]);

        SendSMSMessage::dispatch(
            $validated['phone'],
            $validated['message'],
            $validated['application_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'SMS queued for sending.'
        ]);
    }
}
