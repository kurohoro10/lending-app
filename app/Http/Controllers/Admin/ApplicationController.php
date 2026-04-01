<?php

/**
 * @file    app/Http/Controllers/Admin/ApplicationController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages the full admin-facing lifecycle of loan applications within the
 * commercial loan application system.
 *
 * Responsibilities:
 *  - Listing and filtering applications (with role-scoped visibility)
 *  - Displaying individual applications and auto-marking answered questions as read
 *  - Updating application status with optional internal comment
 *  - Assigning applications to assessors (with automatic status promotion)
 *  - Returning applications to clients with a reason and optional SMS notification
 *  - Exporting application summaries as downloadable PDFs
 *
 * Role behaviour:
 *  - `admin`    — full visibility across all applications and assessors
 *  - `assessor` — scoped to applications assigned to themselves
 *
 * Valid statuses:
 *  draft | submitted | under_review | additional_info_required |
 *  approved | declined | withdrawn
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Events\Application\ApplicationReturned;
use App\Events\Application\ApplicationStatusChanged;
use App\Helpers\ActivityLogFormatter;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Question;
use App\Models\User;
use App\Services\MessagingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Terminal statuses that may not be overwritten once set.
     *
     * Prevents accidental re-opening of approved or declined applications
     * without a deliberate workflow step.
     *
     * @var string[]
     */
    private const LOCKED_STATUSES = ['approved', 'declined'];

    /**
     * Statuses from which an application may be returned to the client.
     *
     * @var string[]
     */
    private const RETURNABLE_STATUSES = ['submitted', 'under_review'];

    /**
     * Valid application status values accepted by updateStatus().
     *
     * @var string[]
     */
    private const VALID_STATUSES = [
        'draft', 'submitted', 'under_review',
        'additional_info_required', 'approved', 'declined', 'withdrawn',
    ];

    // =========================================================================
    // Listing
    // =========================================================================

    /**
     * Display the paginated applications index with optional filtering.
     *
     * Assessors see only applications assigned to themselves. Admins see all
     * applications and may additionally filter by assigned assessor.
     *
     * Includes an unread answered-question badge count scoped to the current
     * user's visible application set.
     *
     * @param  Request  $request  Incoming HTTP request; supports `status`, `search`,
     *                            and `assigned_to` query parameters.
     * @return View               The `admin.applications.index` view.
     *
     * @queryParam string status      Filter by application status.
     * @queryParam string search      Full-text search against application number,
     *                                client name, or client email.
     * @queryParam int    assigned_to Filter by assessor user ID (admin only).
     */
    public function index(Request $request): View
    {
        $query = $this->buildIndexQuery($request);

        $applications = $query->paginate(20);

        $assessors = $this->getAssessorsForCurrentUser();

        $totalAnsweredQuestions = $this->countUnreadAnsweredQuestions();

        return view('admin.applications.index', compact(
            'applications',
            'assessors',
            'totalAnsweredQuestions'
        ));
    }

    // =========================================================================
    // Detail View
    // =========================================================================

    /**
     * Display a single application with all related data loaded.
     *
     * Eager-loads all relationships required by the show view in a single
     * query batch. Automatically marks all unread answered questions as read
     * and logs the review activity when questions are present.
     *
     * @param  Application  $application  The bound application model instance.
     * @return View                       The `admin.applications.show` view.
     */
    public function show(Application $application): View
    {
        $this->loadApplicationRelationships($application);

        $this->markUnreadQuestionsAsRead($application);

        $activityLogs = ActivityLogFormatter::forApplication($application);

        return view('admin.applications.show', compact('application', 'activityLogs'));
    }

    // =========================================================================
    // Status Management
    // =========================================================================

    /**
     * Update the status of an application, optionally adding an internal comment.
     *
     * Terminal statuses (`approved`, `declined`) cannot be overwritten. If a
     * `status_note` is provided, it is persisted as an internal comment linked
     * to the status change. Dispatches `ApplicationStatusChanged` after the
     * database transaction commits successfully.
     *
     * @param  Request      $request      Incoming HTTP request with new status and optional note.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success or error flash message.
     *
     * @bodyParam string status      required  New status value (see VALID_STATUSES).
     * @bodyParam string status_note nullable  Optional internal comment (max 500 chars).
     */
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $this->validateStatusUpdate($request);

        $oldStatus = $application->status;
        $newStatus = $validated['status'];

        if ($this->isStatusLocked($oldStatus)) {
            return back()->with('error', "Cannot change status of a {$oldStatus} application.");
        }

        DB::beginTransaction();
        try {
            $application->update(['status' => $newStatus]);

            $this->maybeAddStatusComment($request, $application, $newStatus);

            ActivityLog::logActivity(
                'status_changed',
                "Status changed from {$oldStatus} to {$newStatus}",
                $application,
                ['old_status' => $oldStatus],
                ['new_status' => $newStatus]
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update application status: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
            return back()->with('error', 'Failed to update status. Please try again.');
        }

        ApplicationStatusChanged::dispatch($application, $oldStatus, $newStatus);

        return back()->with('success', 'Application status updated successfully.');
    }

    // =========================================================================
    // Assignment
    // =========================================================================

    /**
     * Assign the application to an assessor.
     *
     * Requires the `assign` policy gate. Terminal-status applications cannot
     * be reassigned. If the application is currently `submitted`, it is
     * automatically promoted to `under_review` on assignment. Both the
     * assignment and any automatic status change are wrapped in a single
     * database transaction.
     *
     * @param  Request      $request      Incoming HTTP request with the target assessor ID.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success or error flash message.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `assign` policy.
     *
     * @bodyParam int assigned_to required  The ID of the assessor user to assign to.
     */
    public function assign(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('assign', $application);

        if ($this->isStatusLocked($application->status)) {
            return back()->with('error', 'Cannot reassign applications that are approved or declined.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $oldAssignee = $application->assigned_to;
        $newAssignee = $validated['assigned_to'];

        if ($oldAssignee == $newAssignee) {
            return back()->with('info', 'Application is already assigned to this assessor.');
        }

        DB::beginTransaction();
        try {
            $application->update(['assigned_to' => $newAssignee]);

            $this->maybePromoteStatusOnAssignment($application);

            $newAssigneeName = $this->logAssignmentActivity($application, $oldAssignee, $newAssignee);

            DB::commit();

            return back()->with('success', "Application successfully assigned to {$newAssigneeName}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign application: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
            return back()->with('error', 'Failed to assign application. Please try again.');
        }
    }

    // =========================================================================
    // Return to Client
    // =========================================================================

    /**
     * Return an application to the client for amendments.
     *
     * Only applications in `submitted` or `under_review` status may be
     * returned. Sets status to `additional_info_required`, records the reason
     * as a client-visible comment, and dispatches `ApplicationReturned`. If
     * `notify_sms` is true and a mobile number is available, an SMS is also
     * sent to the client.
     *
     * @param  Request      $request      Incoming HTTP request with return reason and SMS flag.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success or error flash message.
     *
     * @bodyParam string  return_reason required  Reason for returning the application (10–1000 chars).
     * @bodyParam boolean notify_sms    nullable  Whether to send an SMS notification to the client.
     */
    public function returnToClient(Request $request, Application $application): RedirectResponse
    {
        if (! in_array($application->status, self::RETURNABLE_STATUSES)) {
            return back()->with('error', 'Only submitted or under review applications can be returned.');
        }

        $application->load('personalDetails', 'user');

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

        ApplicationReturned::dispatch(
            $application,
            $validated['return_reason'],
            $request->boolean('notify_sms')
        );

        $this->maybeSendReturnSms($request, $application, $validated['return_reason']);

        return back()->with('success', 'Application returned to client successfully.');
    }

    // =========================================================================
    // PDF Export
    // =========================================================================

    /**
     * Generate and download a PDF export of the application.
     *
     * Eager-loads all sections required by the PDF view and streams the
     * generated file as a download attachment.
     *
     * @param  Application  $application  The bound application model instance.
     * @return Response                   PDF file download response.
     */
    public function exportPdf(Application $application): Response
    {
        $application->load([
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
            'documents',
            'comments',
            'activityLogs',
        ]);

        $pdf = Pdf::loadView('admin.applications.pdf', [
            'application' => $application,
            'exportDate'  => now(),
            'exportedBy'  => auth()->user(),
        ]);

        return $pdf->download("application-{$application->application_number}.pdf");
    }

    // =========================================================================
    // Private Helpers — Index
    // =========================================================================

    /**
     * Build the base Eloquent query for the applications index.
     *
     * Applies role-scoping, status filter, search filter, and assessor filter
     * based on the authenticated user's role and the provided request parameters.
     *
     * @param  Request  $request  The incoming index request.
     * @return \Illuminate\Database\Eloquent\Builder  Configured query builder.
     */
    private function buildIndexQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Application::with(['user', 'personalDetails', 'assignedTo'])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'answered')->whereNull('read_at');
            }])
            ->latest();

        if (auth()->user()->hasRole('assessor')) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('personalDetails', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('assigned_to') && auth()->user()->hasRole('admin')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        return $query;
    }

    /**
     * Retrieve the assessor list for the current user.
     *
     * Returns all users with the `assessor` role for admins, and an empty
     * collection for assessors (who have no need to view or filter by assessor).
     *
     * @return \Illuminate\Support\Collection  Collection of assessor User models.
     */
    private function getAssessorsForCurrentUser(): \Illuminate\Support\Collection
    {
        return auth()->user()->hasRole('admin')
            ? User::role('assessor')->get()
            : collect();
    }

    /**
     * Count unread answered questions visible to the current user.
     *
     * Assessors see only questions on applications assigned to themselves.
     * Admins see counts across all applications.
     *
     * @return int  Number of answered but unread questions.
     */
    private function countUnreadAnsweredQuestions(): int
    {
        $query = Question::where('status', 'answered')->whereNull('read_at');

        if (auth()->user()->hasRole('assessor')) {
            $query->whereHas('application', function ($q) {
                $q->where('assigned_to', auth()->id());
            });
        }

        return $query->count();
    }

    // =========================================================================
    // Private Helpers — Show
    // =========================================================================

    /**
     * Eager-load all relationships required by the application show view.
     *
     * @param  Application  $application  The application to hydrate.
     * @return void
     */
    private function loadApplicationRelationships(Application $application): void
    {
        $application->load([
            'user',
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses.verifiedBy',
            'documents.uploadedBy',
            'communications',
            'comments.user',
            'questions.askedBy',
            'tasks',
            'declarations',
            'creditChecks',
            'activityLogs.user',
        ]);
    }

    /**
     * Mark all unread answered questions on the application as read and log the activity.
     *
     * Loads the unread answered questions, marks each as read by the current
     * admin, and writes a single activity log entry summarising the count.
     * No activity is logged when there are no unread questions.
     *
     * @param  Application  $application  The application whose questions should be marked.
     * @return void
     */
    private function markUnreadQuestionsAsRead(Application $application): void
    {
        $unreadQuestions = $application->questions()
            ->where('status', 'answered')
            ->whereNull('read_at')
            ->get();

        foreach ($unreadQuestions as $question) {
            $question->markAsRead(auth()->id());
        }

        if ($unreadQuestions->count() > 0) {
            ActivityLog::logActivity(
                'questions_reviewed',
                "Admin reviewed {$unreadQuestions->count()} answered question(s)",
                $application
            );
        }
    }

    // =========================================================================
    // Private Helpers — Status Update
    // =========================================================================

    /**
     * Validate the status update request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `status` and optional `status_note` fields.
     */
    private function validateStatusUpdate(Request $request): array
    {
        return $request->validate([
            'status'      => ['required', 'string', 'in:' . implode(',', self::VALID_STATUSES)],
            'status_note' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /**
     * Determine whether a status value is locked against further changes.
     *
     * @param  string  $status  The current application status.
     * @return bool             True if the status is terminal and cannot be changed.
     */
    private function isStatusLocked(string $status): bool
    {
        return in_array($status, self::LOCKED_STATUSES);
    }

    /**
     * Persist an internal comment when a status note is provided.
     *
     * Skips comment creation silently when the `status_note` field is absent
     * or blank, so the caller does not need to guard against it.
     *
     * @param  Request      $request      The HTTP request (used for sender IP).
     * @param  Application  $application  The application receiving the comment.
     * @param  string       $newStatus    The new status label for the comment body.
     * @return void
     */
    private function maybeAddStatusComment(Request $request, Application $application, string $newStatus): void
    {
        if (! filled($request->input('status_note'))) {
            return;
        }

        $label = ucwords(str_replace('_', ' ', $newStatus));

        $application->comments()->create([
            'user_id'    => auth()->id(),
            'comment'    => "Status changed to {$label}: " . $request->input('status_note'),
            'type'       => 'internal',
            'ip_address' => $request->ip(),
        ]);
    }

    // =========================================================================
    // Private Helpers — Assignment
    // =========================================================================

    /**
     * Promote a `submitted` application to `under_review` on first assignment.
     *
     * Only acts when the application's current status is `submitted`; all
     * other statuses are left unchanged.
     *
     * @param  Application  $application  The application to potentially promote.
     * @return void
     */
    private function maybePromoteStatusOnAssignment(Application $application): void
    {
        if ($application->status !== 'submitted') {
            return;
        }

        $application->update(['status' => 'under_review']);

        ActivityLog::logActivity(
            'status_changed',
            'Status automatically changed to under_review when application was assigned',
            $application,
            ['old_status' => 'submitted'],
            ['new_status' => 'under_review']
        );
    }

    /**
     * Log the assignment activity and return the new assignee's display name.
     *
     * @param  Application  $application  The application being assigned.
     * @param  int|null     $oldAssignee  The previous assignee's user ID, or null if unassigned.
     * @param  int          $newAssignee  The new assignee's user ID.
     * @return string                     The display name of the new assignee.
     */
    private function logAssignmentActivity(Application $application, ?int $oldAssignee, int $newAssignee): string
    {
        $oldAssigneeName = $oldAssignee ? User::find($oldAssignee)?->name : 'Unassigned';
        $newAssigneeName = User::find($newAssignee)?->name ?? 'Unknown';

        $description = "Application assigned to {$newAssigneeName}"
            . ($oldAssignee ? " (previously: {$oldAssigneeName})" : '');

        ActivityLog::logActivity(
            'assigned',
            $description,
            $application,
            ['old_assignee' => $oldAssignee],
            ['new_assignee' => $newAssignee]
        );

        return $newAssigneeName;
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
     * Updates the application status, records the return metadata, and
     * creates a client-visible comment with the provided reason.
     *
     * @param  Request      $request      The HTTP request (used for sender IP).
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

        $application->comments()->create([
            'user_id'    => auth()->id(),
            'comment'    => 'Application returned for amendments: ' . $validated['return_reason'],
            'type'       => 'client_visible',
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::logActivity(
            'returned_to_client',
            'Application returned to client: ' . $validated['return_reason'],
            $application
        );
    }

    /**
     * Send an SMS notification to the client if the flag is set and a number is available.
     *
     * Failures are caught and logged without surfacing an HTTP error, so an SMS
     * delivery issue does not roll back the return action.
     *
     * @param  Request      $request       The HTTP request carrying the `notify_sms` boolean.
     * @param  Application  $application   The application that was returned.
     * @param  string       $returnReason  The reason included in the SMS body.
     * @return void
     */
    private function maybeSendReturnSms(Request $request, Application $application, string $returnReason): void
    {
        if (! $request->boolean('notify_sms') || ! $application->personalDetails?->mobile_phone) {
            return;
        }

        try {
            $phone   = $application->personalDetails->mobile_phone;
            $message = "Your loan application #{$application->application_number} has been returned for amendments. "
                     . "Reason: {$returnReason}. Please log in to update and resubmit.";

            app(MessagingService::class)->send($phone, $message, $application);

        } catch (\Exception $e) {
            Log::error('Failed to send return SMS', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }
}