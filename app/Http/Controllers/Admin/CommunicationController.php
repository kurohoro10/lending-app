<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\Communication\CommunicationTemplateService;
use App\Services\MessagingService;
use Illuminate\Support\Facades\DB;

/**
 * Class CommunicationController
 *
 * Manages outbound communications (Email and SMS) to applicants.
 * Includes template retrieval and history logging for audit purposes.
 *
 * @package App\Http\Controllers\Admin
 */
class CommunicationController extends Controller
{
    /**
     * Display a listing of communications for a specific application.
     *
     * @param Request     $request
     * @param Application $application
     * @return View
     */
    public function index(Request $request, Application $application): View
    {
        $query = $application->communications()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $communications = $query->latest()->paginate(20);

        return view('admin.communications.index', compact('application', 'communications'));
    }

    /**
     * Display the specified communication record.
     *
     * @param Communication $communication
     * @return View
     */
    public function show(Communication $communication): View
    {
        $communication->load(['application', 'user']);

        return view('admin.communications.show', compact('communication'));
    }

    /**
     * Retrieve available email templates for the given application context.
     *
     * @param Application $application
     * @return JsonResponse
     */
    public function getEmailTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getEmailTemplates($application);

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Retrieve available SMS templates for the given application context.
     *
     * @param Application $application
     * @return JsonResponse
     */
    public function getSMSTemplates(Application $application): JsonResponse
    {
        $templates = CommunicationTemplateService::getSMSTemplates($application);

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Dispatch a custom email to the client and log the outbound communication.
     *
     * @param Request     $request
     * @param Application $application
     * @return JsonResponse
     */
    public function sendEmail(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            // Dispatch Notification
            $application->user->notify(
                new \App\Notifications\Admin\CustomEmail($application, $validated['subject'], $validated['message'])
            );

            // Log to communications audit trail
            Communication::create([
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'type'           => 'email_out',
                'direction'      => 'outbound',
                'from_address'   => config('mail.from.address'),
                'to_address'     => $application->user->email,
                'subject'        => $validated['subject'],
                'body'           => $validated['message'],
                'status'         => 'sent',
                'sent_at'        => now(),
                'sender_ip'      => $request->ip(),
            ]);

            ActivityLog::logActivity('email_sent', 'Email sent to client', $application);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $application->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check logs for details.',
            ], 500);
        }
    }

    /**
     * Dispatch an SMS to the client via Twilio and log the activity.
     *
     * @param Request     $request
     * @param Application $application
     * @return JsonResponse
     */
    public function sendSms(Request $request, Application $application): JsonResponse
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
            Log::error('Failed to send SMS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS. Service may be temporarily unavailable.',
            ], 500);
        }
    }

    /**
     * Return application to client for amendments
     */
    public function returnToClient(Request $request, Application $application)
    {
        // Load relationships
        $application->load('personalDetails', 'user');

        if (!in_array($application->status, ['submitted', 'under_review'])) {
            return back()->with('error', 'Only submitted or under review applications can be returned.');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|min:10|max:1000',
            'notify_sms'    => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $application->update([
                'status'        => 'additional_info_required',
                'return_reason' => $validated['return_reason'],
                'returned_at'   => now(),
                'returned_by'   => auth()->id(),
            ]);

            ActivityLog::logActivity(
                'returned_to_client',
                'Application returned to client: ' . $validated['return_reason'],
                $application
            );

            // Add visible comment to client
            $application->comments()->create([
                'user_id'           => auth()->id(),
                'comment'           => 'Application returned for amendments: ' . $validated['return_reason'],
                'is_internal'       => false,
                'is_client_visible' => true,
                'commenter_ip'      => $request->ip(),
            ]);

            DB::commit();

            \Log::info('Application returned to client', [
                'application_id' => $application->id,
                'notify_sms' => $request->boolean('notify_sms'),
                'has_phone' => $application->personalDetails?->mobile_phone !== null,
                'phone' => $application->personalDetails?->mobile_phone,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to return application: ' . $e->getMessage());
            return back()->with('error', 'Failed to return application. Please try again.');
        }

        // Notifications outside transaction
        try {
            // Send email notification
            $application->user->notify(
                new \App\Notifications\Application\ApplicationReturned($application)
            );
            \Log::info('Return email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send return email: ' . $e->getMessage());
        }

        // SMS notification
        if ($request->boolean('notify_sms')) {
            \Log::info('SMS notification requested', [
                'has_phone' => $application->personalDetails?->mobile_phone !== null,
                'phone' => $application->personalDetails?->mobile_phone,
            ]);

            if ($application->personalDetails?->mobile_phone) {
                try {
                    $phone = $application->personalDetails->mobile_phone;
                    $message = "Your loan application #{$application->application_number} has been returned for amendments. Reason: {$validated['return_reason']}. Please log in to update and resubmit.";

                    \Log::info('Attempting to send SMS', [
                        'phone' => $phone,
                        'message_length' => strlen($message),
                    ]);

                    $result = app(MessagingService::class)->send(
                        $phone,
                        $message,
                        $application
                    );

                    \Log::info('SMS sent successfully', [
                        'result' => $result,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send return SMS', [
                        'phone' => $phone ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Log::warning('Cannot send SMS - no phone number', [
                    'application_id' => $application->id,
                    'has_personal_details' => $application->personalDetails !== null,
                ]);
            }
        } else {
            \Log::info('SMS notification not requested (checkbox not checked)');
        }

        return back()->with('success', 'Application returned to client successfully.');
    }
}
