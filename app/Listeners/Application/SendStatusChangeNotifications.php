<?php

namespace App\Listeners\Application;

use App\Events\Application\ApplicationStatusChanged;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;

class SendStatusChangeNotifications
{
    public function __construct(protected MessagingService $messaging) {}

    public function handle(ApplicationStatusChanged $event): void
    {
        $application = $event->application;
        $phone       = $application->personalDetails?->mobile_phone;
        $number      = $application->application_number;

        try {
            match ($event->newStatus) {
                'under_review'             => $this->underReview($application, $phone, $number),
                'approved'                 => $this->approved($application, $phone, $number),
                'declined'                 => $this->declined($application, $phone, $number),
                'additional_info_required' => $this->additionalInfo($application, $phone, $number),
                default                    => null,
            };
        } catch (\Exception $e) {
            // Log but don't bubble — a notification failure should never break the status update
            Log::error('SendStatusChangeNotifications failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'new_status'     => $event->newStatus,
            ]);
        }
    }

    private function underReview($application, $phone, $number): void
    {
        $application->user->notify(
            new \App\Notifications\Application\ApplicationUnderReview($application)
        );

        if ($phone) {
            $this->messaging->send(
                $phone,
                "Your loan application #{$number} is now under review. - Loan Team",
                $application
            );
        }
    }

    private function approved($application, $phone, $number): void
    {
        $application->user->notify(
            new \App\Notifications\Application\ApplicationApproved($application)
        );

        if ($phone) {
            $this->messaging->send(
                $phone,
                "Congratulations! Your loan application #{$number} has been APPROVED! - Loan Team",
                $application
            );
        }
    }

    private function declined($application, $phone, $number): void
    {
        $application->user->notify(
            new \App\Notifications\Application\ApplicationDeclined($application, 'Application declined after review')
        );

        if ($phone) {
            $this->messaging->send(
                $phone,
                "Regarding your loan application #{$number} — we're unable to proceed. Check email for details. - Loan Team",
                $application
            );
        }
    }

    private function additionalInfo($application, $phone, $number): void
    {
        $application->user->notify(
            new \App\Notifications\Application\ApplicationAdditionalInfoRequired($application)
        );

        if ($phone) {
            $this->messaging->send(
                $phone,
                "We need additional information for your application #{$number}. Please log in. - Loan Team",
                $application
            );
        }
    }
}
