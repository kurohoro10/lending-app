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
use App\Services\TwilioService;

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
            app(TwilioService::class)->sendSMS(
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
}
