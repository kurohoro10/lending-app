<?php

/**
 * @file    app/Http/Controllers/Admin/Communication/AdHocCommunicationController.php
 * @package App\Http\Controllers\Admin\Communication
 *
 * Handles ad-hoc (freeform recipient) email and SMS communications
 * for the Admin panel. Unlike the standard communication controllers,
 * the recipient here is not the application's owner but an arbitrary
 * third party chosen by the admin (e.g. accountant, solicitor, director).
 *
 * All communications are still tied to the application for audit purposes,
 * and are flagged with metadata['is_ad_hoc' => true] so the thread view
 * can visually distinguish them from applicant-directed messages.
 */

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Mail\Admin\AdHocEmail;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Communication;
use App\Services\Communication\CommunicationTemplateService;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdHocCommunicationController extends Controller
{
    // =========================================================================
    // Templates
    // =========================================================================

    /**
     * Return ad-hoc email templates for the compose UI.
     */
    public function emailTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getAdHocEmailTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Return ad-hoc SMS templates for the compose UI.
     */
    public function smsTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getAdHocSmsTemplates($application);

        return response()->json([
            'success'   => true,
            'templates' => $templates,
        ]);
    }

    // =========================================================================
    // Send Email
    // =========================================================================

    /**
     * Send an ad-hoc email to a freeform recipient and log the communication.
     *
     * @bodyParam string recipient_name  required  Display name of the recipient.
     * @bodyParam string recipient_email required  Email address to send to.
     * @bodyParam string subject         required  Email subject (max 255).
     * @bodyParam string message         required  Email body (max 5000).
     *
     * @response 200 { "success": true, "message": "Email sent successfully to john@example.com" }
     * @response 500 { "success": false, "message": "Failed to send email." }
     */
    public function sendEmail(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'recipient_name'  => 'required|string|max:255',
            'recipient_email' => 'required|email|max:255',
            'subject'         => 'required|string|max:255',
            'message'         => 'required|string|max:5000',
        ]);

        try {
            Mail::to($validated['recipient_email'], $validated['recipient_name'])
                ->bcc(config('mail.archive_email'))
                ->send(new AdHocEmail(
                    application:   $application,
                    recipientName: $validated['recipient_name'],
                    emailSubject:  $validated['subject'],
                    messageBody:   $validated['message'],
                ));

            Communication::create([
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'type'           => 'email_out',
                'direction'      => 'outbound',
                'from_address'   => config('mail.from.address'),
                'to_address'     => $validated['recipient_email'],
                'subject'        => $validated['subject'],
                'body'           => $validated['message'],
                'status'         => 'sent',
                'sent_at'        => now(),
                'sender_ip'      => $request->ip(),
                'metadata'       => [
                    'is_ad_hoc'      => true,
                    'recipient_name' => $validated['recipient_name'],
                ],
            ]);

            ActivityLog::logActivity(
                'ad_hoc_email_sent',
                'Ad-hoc email sent to ' . $validated['recipient_email'],
                $application,
                null,
                [
                    'direction' => 'outbound',
                    'subject'   => $validated['subject'],
                    'to'        => $validated['recipient_email'],
                    'excerpt'   => Str::limit($validated['message'], 150),
                    'status'    => 'sent',
                    'is_ad_hoc' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $validated['recipient_email'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send ad-hoc email', [
                'application_id'  => $application->id,
                'recipient_email' => $validated['recipient_email'],
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check logs for details.',
            ], 500);
        }
    }

    // =========================================================================
    // Send SMS
    // =========================================================================

    /**
     * Send an ad-hoc SMS to a freeform phone number and log the communication.
     *
     * @bodyParam string recipient_name  required  Display name of the recipient.
     * @bodyParam string recipient_phone required  Phone number in E.164 format.
     * @bodyParam string message         required  SMS body (max 1000).
     *
     * @response 200 { "success": true, "message": "SMS sent successfully to +61400000000" }
     * @response 500 { "success": false, "message": "Failed to send SMS." }
     */
    public function sendSms(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'recipient_name'  => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'message'         => 'required|string|max:1000',
        ]);

        try {
            $result = app(MessagingService::class)->send(
                $validated['recipient_phone'],
                $validated['message'],
                $application,
            );
            $sent = $result['success'] ?? false;

            Communication::create([
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'type'           => 'sms_out',
                'direction'      => 'outbound',
                'from_address'   => config('services.twilio.from'),
                'to_address'     => $validated['recipient_phone'],
                'body'           => $validated['message'],
                'status'         => 'sent',
                'sent_at'        => now(),
                'sender_ip'      => $request->ip(),
                'metadata'       => [
                    'is_ad_hoc'      => true,
                    'recipient_name' => $validated['recipient_name'],
                ],
            ]);

            ActivityLog::logActivity(
                'ad_hoc_sms_sent',
                'Ad-hoc SMS sent to ' . $validated['recipient_phone'],
                $application,
                null,
                array_filter([
                    'direction'   => 'outbound',
                    'to'          => $validated['recipient_phone'],
                    'excerpt'     => Str::limit($validated['message'], 150),
                    'status'      => $sent ? ($result['status'] ?? 'sent') : 'sent',
                    'message_sid' => $sent ? ($result['message_sid'] ?? null) : null,
                    'is_ad_hoc'   => true,
                ], fn ($value) => ! is_null($value))
            );

            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully to ' . $validated['recipient_phone'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send ad-hoc SMS', [
                'application_id'  => $application->id,
                'recipient_phone' => $validated['recipient_phone'],
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS. Service may be temporarily unavailable.',
            ], 500);
        }
    }
}