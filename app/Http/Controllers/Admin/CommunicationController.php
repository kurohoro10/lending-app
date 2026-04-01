<?php

/**
 * @file    app/Http/Controllers/Admin/CommunicationController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages the admin-facing view and operational actions for communications
 * linked to loan applications within the commercial loan application system.
 *
 * Responsibilities:
 *  - Listing and filtering communications for a specific application
 *  - Displaying individual communication records
 *  - Returning an application to the client with an email and optional SMS notification
 *  - Bulk-marking all unread inbound messages on a channel as read
 *
 * Supported communication channels for markChannelRead():
 *  - `email` — covers type: email_in
 *  - `sms`   — covers types: sms_in, whatsapp_in
 *
 * Returnable application statuses:
 *  - `submitted`    — application awaiting initial review
 *  - `under_review` — application currently being assessed
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Communication;
use App\Notifications\Application\ApplicationReturned;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CommunicationController extends Controller
{
    /**
     * Statuses from which an application may be returned to the client.
     *
     * @var string[]
     */
    private const RETURNABLE_STATUSES = ['submitted', 'under_review'];

    /**
     * Inbound communication type identifiers mapped to each channel.
     *
     * Used by markChannelRead() to scope the bulk read update to the
     * correct message types for a given channel name.
     *
     * @var array<string, string[]>
     */
    private const CHANNEL_TYPES = [
        'email' => ['email_in'],
        'sms'   => ['sms_in', 'whatsapp_in'],
    ];

    // =========================================================================
    // Listing & Display
    // =========================================================================

    /**
     * Display a paginated listing of communications for a specific application.
     *
     * Supports optional filtering by communication `type` via query parameter.
     * Results are ordered newest-first and eager-load the sending user.
     *
     * @param  Request      $request      Incoming HTTP request; supports `?type=` query param.
     * @param  Application  $application  The bound application model instance.
     * @return View                       The `admin.communications.index` view.
     *
     * @queryParam string type  Optional communication type filter (e.g. `email_out`, `sms_in`).
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
     * Display a single communication record with its related application and user.
     *
     * @param  Communication  $communication  The bound communication model instance.
     * @return View                           The `admin.communications.show` view.
     */
    public function show(Communication $communication): View
    {
        $communication->load(['application', 'user']);

        return view('admin.communications.show', compact('communication'));
    }

    // =========================================================================
    // Return to Client
    // =========================================================================

    /**
     * Return an application to the client for amendments.
     *
     * Only applications in `submitted` or `under_review` status may be returned.
     * Within a single transaction: updates the application status, logs the
     * activity, and creates a client-visible comment with the return reason.
     *
     * After the transaction commits, sends an email notification and, when
     * `notify_sms` is true and a mobile number is available, an SMS. Both
     * notifications are dispatched outside the transaction and failures are
     * logged without rolling back the status change.
     *
     * @param  Request      $request      Incoming HTTP request with return reason and SMS flag.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success or error flash message.
     *
     * @bodyParam string  return_reason required  Reason for returning the application (10–1000 chars).
     * @bodyParam boolean notify_sms    nullable  Whether to send an SMS to the client.
     */
    public function returnToClient(Request $request, Application $application): RedirectResponse
    {
        $application->load('personalDetails', 'user');

        if (! in_array($application->status, self::RETURNABLE_STATUSES)) {
            return back()->with('error', 'Only submitted or under review applications can be returned.');
        }

        $validated = $this->validateReturnToClient($request);

        DB::beginTransaction();
        try {
            $this->applyReturnToClient($request, $application, $validated);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to return application: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
            return back()->with('error', 'Failed to return application. Please try again.');
        }

        $this->sendReturnEmailNotification($application);
        $this->maybeSendReturnSms($request, $application, $validated['return_reason']);

        return back()->with('success', 'Application returned to client successfully.');
    }

    // =========================================================================
    // Mark Channel Read
    // =========================================================================

    /**
     * Bulk-mark all unread inbound messages on a given channel as read.
     *
     * Determines the relevant communication types from the CHANNEL_TYPES map
     * and updates matching records in a single query.
     *
     * @param  Request      $request      Incoming HTTP request with channel identifier.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Simple success acknowledgement.
     *
     * @bodyParam string channel required  Channel to mark — `email` or `sms`.
     *
     * @response 200 { "success": true }
     */
    public function markChannelRead(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'channel' => ['required', 'in:email,sms'],
        ]);

        $types = self::CHANNEL_TYPES[$validated['channel']];

        $application->communications()
            ->whereNull('read_at')
            ->where('direction', 'inbound')
            ->whereIn('type', $types)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Private Helpers — Return to Client
    // =========================================================================

    /**
     * Validate the return-to-client request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `return_reason` and optional `notify_sms` fields.
     */
    private function validateReturnToClient(Request $request): array
    {
        return $request->validate([
            'return_reason' => ['required', 'string', 'min:10', 'max:1000'],
            'notify_sms'    => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Apply the return-to-client state changes within an open database transaction.
     *
     * Updates the application status and return metadata, logs the activity,
     * and creates a client-visible comment with the return reason.
     *
     * @param  Request      $request      The HTTP request (used for commenter IP).
     * @param  Application  $application  The application to return.
     * @param  array        $validated    Validated return reason payload.
     * @return void
     */
    private function applyReturnToClient(Request $request, Application $application, array $validated): void
    {
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

        $application->comments()->create([
            'user_id'           => auth()->id(),
            'comment'           => 'Application returned for amendments: ' . $validated['return_reason'],
            'is_internal'       => false,
            'is_client_visible' => true,
            'commenter_ip'      => $request->ip(),
        ]);
    }

    /**
     * Send the ApplicationReturned email notification to the client.
     *
     * Failures are caught and logged without surfacing an HTTP error, ensuring
     * a delivery issue does not roll back the return action.
     *
     * @param  Application  $application  The application that was returned.
     * @return void
     */
    private function sendReturnEmailNotification(Application $application): void
    {
        try {
            $application->user->notify(new ApplicationReturned($application));
        } catch (\Exception $e) {
            Log::error('Failed to send return email: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }
    }

    /**
     * Send an SMS notification to the client if the flag is set and a number is available.
     *
     * Failures are caught and logged without surfacing an HTTP error.
     *
     * @param  Request      $request       The HTTP request carrying the `notify_sms` boolean.
     * @param  Application  $application   The application that was returned.
     * @param  string       $returnReason  The return reason included in the SMS body.
     * @return void
     */
    private function maybeSendReturnSms(Request $request, Application $application, string $returnReason): void
    {
        if (! $request->boolean('notify_sms') || ! $application->personalDetails?->mobile_phone) {
            return;
        }

        try {
            $message = "Your loan application #{$application->application_number} has been returned for amendments. "
                     . "Reason: {$returnReason}. Please log in to update and resubmit.";

            app(MessagingService::class)->send(
                $application->personalDetails->mobile_phone,
                $message,
                $application
            );
        } catch (\Exception $e) {
            Log::error('Failed to send return SMS: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }
    }
}