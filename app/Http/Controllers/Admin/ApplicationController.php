<?php

namespace App\Http\Controllers\Admin;

use App\Events\Application\ApplicationReturned;
use App\Events\Application\ApplicationStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Question;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Services\MessagingService;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Application::with(['user', 'personalDetails', 'assignedTo'])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'answered')
                    ->whereNull('read_at');
            }])
            ->latest();

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

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $applications = $query->paginate(20);
        $assessors    = User::role('assessor')->get();

        $totalAnsweredQuestions = Question::where('status', 'answered')->whereNull('read_at')->count();

        return view('admin.applications.index', compact('applications', 'assessors', 'totalAnsweredQuestions'));
    }

    public function show(Application $application): View
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

        // Auto-mark all answered questions as read when admin views the application
        $unreadQuestions = $application->questions()
            ->where('status', 'answered')
            ->whereNull('read_at')
            ->get();

        foreach ($unreadQuestions as $question) {
            $question->markAsRead(auth()->id());
        }

        // Log this activity
        if ($unreadQuestions->count() > 0) {
            ActivityLog::logActivity(
                'questions_reviewed',
                "Admin reviewed {$unreadQuestions->count()} answered question(s)",
                $application
            );
        }

        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:draft,submitted,under_review,additional_info_required,approved,declined,withdrawn',
            ],
            'status_note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $application->status;
        $newStatus = $validated['status'];

        if ($oldStatus === 'approved' && $newStatus !== 'approved') {
            return back()->with('error', 'Cannot change status of an approved application.');
        }

        if ($oldStatus === 'declined' && $newStatus !== 'declined') {
            return back()->with('error', 'Cannot change status of a declined application.');
        }

        DB::beginTransaction();
        try {
            $application->update(['status' => $newStatus]);

            $statusNote = $validated['status_note'] ?? null;

            if (filled($statusNote)) {
                $application->comments()->create([
                    'user_id'    => auth()->id(),
                    'comment'    => 'Status changed to ' . ucwords(str_replace('_', ' ', $newStatus)) . ': ' . $statusNote,
                    'type'       => 'internal',
                    'ip_address' => $request->ip(),
                ]);
            }

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

    public function assign(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $application->update($validated);

        ActivityLog::logActivity('assigned', 'Application assigned to assessor', $application);

        return back()->with('success', 'Application assigned successfully.');
    }

    public function returnToClient(Request $request, Application $application): RedirectResponse
    {
        if (!in_array($application->status, ['submitted', 'under_review'])) {
            return back()->with('error', 'Only submitted or under review applications can be returned.');
        }

        $application->load('personalDetails', 'user');

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

                    $result = app(MessagingService::class)->send( // CHANGE THIS
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
}
