<?php

namespace App\Listeners\Application;

use App\Events\Application\ApplicationReturned;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;

class SendReturnNotifications
{
    public function __construct(protected MessagingService $messaging) {}

    public function handle(ApplicationReturned $event): void
    {
        $application = $event->application;

        try {
            $application->user->notify(
                new \App\Notifications\Application\ApplicationReturned($application)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send return email: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }

        if ($event->sendSms && $application->personalDetails?->mobile_phone) {
            try {
                $this->messaging->send(
                    $application->personalDetails->mobile_phone,
                    "Your application #{$application->application_number} has been returned. Reason: {$event->reason}.",
                    $application
                );
            } catch (\Exception $e) {
                Log::error('Failed to send return SMS: ' . $e->getMessage(), [
                    'application_id' => $application->id,
                ]);
            }
        }
    }
}
