<?php

/**
 * File: app/Actions/Application/SubmitApplication.php
 * Description: Handles full application submission workflow including
 *              validation, auto-decline logic, status updates,
 *              logging, and notification dispatching.
 */

namespace App\Actions\Application;

use App\Models\Application;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\Application\ApplicationNotificationService;
use App\Services\AutoDeclineService;
use Illuminate\Support\Facades\DB;

class SubmitApplication
{
    public function handle(Application $application, array $signatureData): void
    {
        \Log::info('SubmitApplication action started', [
            'application_id' => $application->id,
            'signature_data_keys' => array_keys($signatureData),
            'has_signature' => !empty($signatureData['signature']),
        ]);

        DB::transaction(function () use ($application, $signatureData) {

            \Log::info('Creating signature declaration...');

            // Store final declaration
            $application->declarations()->create([
                'declaration_type'   => 'final_submission',
                'declaration_text'   => 'Final submission declaration.',
                'is_agreed'          => true,
                'agreed_at'          => now(),
                'agreement_ip'       => request()->ip(),
                'signature_data'     => $signatureData['signature'],
                'signature_type'     => $signatureData['signature_type'] ?? 'typed',
                'signatory_name'     => auth()->user()->name,
                'signatory_position' => $signatureData['signatory_position'] ?? null,
                'signature_timestamp'=> now(),
            ]);

            \Log::info('Signature declaration created', [
                'declaration_id' => $application->id,
            ]);

            $declineCheck = AutoDeclineService::checkDeclineCriteria($application);

            if ($declineCheck['should_decline']) {
                $this->autoDecline($application, $declineCheck['reason']);
                return;
            }

            $application->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
                'submission_ip'=> request()->ip(),
            ]);

            ActivityLog::logActivity(
                'submitted',
                'Application submitted for review',
                $application
            );
        });

        // Reload relationships before sending notifications
        $application->load('personalDetails', 'user');

        // Notifications outside transaction
        app(ApplicationNotificationService::class)
            ->handleSubmitted($application);
    }

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
                'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            ]
        );

        $application->comments()->create([
            'user_id'          => $systemUser->id,
            'comment'          => 'AUTO-DECLINE: ' . $reason,
            'is_internal'      => true,
            'is_client_visible'=> false,
            'commenter_ip'     => request()->ip(),
        ]);

        // Reload before sending notifications
        $application->load('personalDetails', 'user');

        app(ApplicationNotificationService::class)
            ->handleDeclined($application, $reason);
    }
}
