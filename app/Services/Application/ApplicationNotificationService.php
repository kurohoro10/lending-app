<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\User;
use App\Notifications\Application\ApplicationSubmitted;
use App\Notifications\Application\ApplicationDeclined;
use App\Notifications\Admin\NewApplicationSubmittedAdmin;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class ApplicationNotificationService
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function handleSubmitted(Application $application): void
    {
        // Send email notification (catch separately so it doesn't block SMS)
        try {
            $application->user->notify(
                new ApplicationSubmitted($application)
            );
            Log::info('Email notification sent successfully');
        } catch (\Exception $e) {
            Log::error('Email notification failed (but continuing with SMS): ' . $e->getMessage());
            // Don't throw - continue to SMS
        }

        // Send SMS (independent of email)
        try {
            $this->sendSMS(
                $application,
                "Your application #{$application->application_number} has been submitted successfully. We'll review it within 24-48 hours."
            );
            Log::info('SMS notification sent successfully');
        } catch (\Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
        }

        // Notify admins (independent of client notifications)
        try {
            $admins = User::role('admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewApplicationSubmittedAdmin($application));
            }
        } catch (\Exception $e) {
            Log::error('Admin notification failed: ' . $e->getMessage());
        }
    }

    public function handleDeclined(Application $application, string $reason): void
    {
        try {
            // Send email notification
            $application->user->notify(
                new ApplicationDeclined($application, $reason)
            );

            // Send SMS immediately (no queue)
            $this->sendSMS(
                $application,
                "Your application #{$application->application_number} has been declined. Reason: {$reason}"
            );

        } catch (\Exception $e) {
            Log::error('Decline notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS immediately (synchronous, no queue)
     */
    protected function sendSMS(Application $application, string $message): void
    {
        Log::info('sendSMS called', [
            'application_id' => $application->id,
            'has_personal_details' => $application->personalDetails !== null,
        ]);

        // Reload personal details to ensure we have latest data
        $application->load('personalDetails');

        if (!$application->personalDetails) {
            Log::warning('No personal details found', [
                'application_id' => $application->id
            ]);
            return;
        }

        $phone = $application->personalDetails->mobile_phone;

        if (!$phone) {
            Log::warning('No mobile phone found', [
                'application_id' => $application->id,
                'personal_details_id' => $application->personalDetails->id,
            ]);
            return;
        }

        try {
            // Send SMS synchronously using WhatsApp (since you're using sandbox)
            $this->twilioService->sendSMS($phone, $message, $application);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
