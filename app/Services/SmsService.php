<?php

namespace App\Services;

use App\Models\Communication;
use App\Models\Application;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilio;

    public function __construct()
    {
        if (config('services.twilio.sid') && config('services.twilio.token')) {
            $this->twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }
    }

    /**
     * Send an SMS and log the communication
     */
    public function send(
        Application $application,
        string $to,
        string $body
    ): Communication {
        $from = config('services.twilio.from');

        // Create communication record
        $communication = $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'sms_out',
            'direction' => 'outbound',
            'from_address' => $from,
            'to_address' => $to,
            'body' => $body,
            'status' => 'pending',
            'sender_ip' => request()->ip(),
        ]);

        try {
            if (!$this->twilio) {
                throw new \Exception('Twilio not configured');
            }

            // Send SMS via Twilio
            $message = $this->twilio->messages->create($to, [
                'from' => $from,
                'body' => $body,
            ]);

            // Update communication status
            $communication->update([
                'status' => 'sent',
                'sent_at' => now(),
                'external_id' => $message->sid,
                'metadata' => [
                    'message_sid' => $message->sid,
                    'status' => $message->status,
                ],
            ]);

            Log::info("SMS sent to {$to}");

        } catch (\Exception $e) {
            // Update communication with error
            $communication->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to send SMS to {$to}: {$e->getMessage()}");

            throw $e;
        }

        return $communication;
    }

    /**
     * Log an incoming SMS
     */
    public function logIncoming(
        Application $application,
        string $from,
        string $body,
        ?string $messageSid = null
    ): Communication {
        return $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'sms_in',
            'direction' => 'inbound',
            'from_address' => $from,
            'to_address' => config('services.twilio.from'),
            'body' => $body,
            'status' => 'delivered',
            'delivered_at' => now(),
            'external_id' => $messageSid,
        ]);
    }

    /**
     * Send application submitted SMS
     */
    public function sendApplicationSubmitted(Application $application): void
    {
        $personalDetails = $application->personalDetails;

        $body = "Your loan application #{$application->application_number} has been submitted successfully. We will contact you if we need additional information.";

        $this->send($application, $personalDetails->mobile_phone, $body);
    }

    /**
     * Send document reminder SMS
     */
    public function sendDocumentReminder(Application $application, string $documentType): void
    {
        $personalDetails = $application->personalDetails;

        $body = "Reminder: Please upload {$documentType} for your loan application #{$application->application_number}. Log in to your portal to upload.";

        $this->send($application, $personalDetails->mobile_phone, $body);
    }

    /**
     * Send question reminder SMS
     */
    public function sendQuestionReminder(Application $application): void
    {
        $personalDetails = $application->personalDetails;

        $pendingQuestions = $application->questions()->pending()->count();

        $body = "You have {$pendingQuestions} pending question(s) for application #{$application->application_number}. Please log in to respond.";

        $this->send($application, $personalDetails->mobile_phone, $body);
    }

    /**
     * Update SMS delivery status from webhook
     */
    public function updateDeliveryStatus(string $messageSid, string $status): void
    {
        $communication = Communication::where('external_id', $messageSid)->first();

        if ($communication) {
            $statusMap = [
                'delivered' => 'delivered',
                'sent' => 'sent',
                'failed' => 'failed',
                'undelivered' => 'failed',
            ];

            $communication->update([
                'status' => $statusMap[$status] ?? $status,
                'delivered_at' => in_array($status, ['delivered', 'sent']) ? now() : null,
                'metadata' => array_merge($communication->metadata ?? [], [
                    'delivery_status' => $status,
                    'updated_at' => now()->toDateTimeString(),
                ]),
            ]);

            Log::info("SMS delivery status updated for {$messageSid}: {$status}");
        }
    }
}
