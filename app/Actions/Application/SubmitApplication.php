<?php

namespace App\Actions\Application;

use App\Models\Application;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\Application\ApplicationNotificationService;
use App\Services\AutoDeclineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmitApplication
{
    /**
     * Execute the application submission logic.
     *
     * NOTE: By the time this is called from ApplicationController::submit(),
     * the 'final_submission' declaration has already been saved. That is
     * intentional — canBeSubmitted() requires it to exist before we can
     * proceed. Do NOT re-create it here.
     *
     * @param Application $application   The application instance being submitted.
     * @param array{
     *   signature: string,
     *   signature_type?: string,
     *   signatory_position?: string
     * } $signatureData Metadata regarding the user's digital signature.
     * @return void
     * @throws \Throwable
     */
    public function handle(Application $application, array $signatureData): void
    {
        DB::transaction(function () use ($application, $signatureData) {

            // ✅ FIX: Declaration creation REMOVED from here.
            //
            // Previously this created a second 'final_submission' declaration,
            // duplicating the one already saved in ApplicationController::submit()
            // (which must happen first so canBeSubmitted() passes).
            //
            // The controller is the single source of truth for the declaration.

            $declineCheck = AutoDeclineService::checkDeclineCriteria($application);

            if ($declineCheck['should_decline']) {
                $this->autoDecline($application, $declineCheck['reason']);
                return;
            }

            $application->update([
                'status'        => 'submitted',
                'submitted_at'  => now(),
                'submission_ip' => request()->ip(),
                'return_reason' => null,
                'returned_at'   => null,
                'returned_by'   => null,
            ]);

            ActivityLog::logActivity(
                'submitted',
                'Application submitted for review',
                $application
            );
        });

        // Reload relationships before sending notifications
        $application->load('personalDetails', 'user');

        // Notifications outside transaction to prevent delay/locking
        app(ApplicationNotificationService::class)
            ->handleSubmitted($application);
    }

    /**
     * Transition the application to a declined state automatically.
     *
     * @param Application $application The application instance to decline.
     * @param string      $reason      The reason for the automatic decline.
     * @return void
     */
    protected function autoDecline(Application $application, string $reason): void
    {
        $application->update([
            'status'       => 'declined',
            'submitted_at' => now(),
            'submission_ip'=> request()->ip(),
        ]);

        ActivityLog::logActivity(
            'auto_declined',
            'Application auto-declined',
            $application
        );

        $systemUser = User::firstOrCreate(
            ['email' => 'system@internal.local'],
            [
                'name'     => 'System',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        $application->comments()->create([
            'user_id'           => $systemUser->id,
            'comment'           => 'AUTO-DECLINE: ' . $reason,
            'is_internal'       => true,
            'is_client_visible' => false,
            'commenter_ip'      => request()->ip(),
        ]);

        // Reload before sending notifications
        $application->load('personalDetails', 'user');

        app(ApplicationNotificationService::class)
            ->handleDeclined($application, $reason);
    }
}
