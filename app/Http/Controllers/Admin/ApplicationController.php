<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\MessagingService;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Application::with(['user', 'personalDetails', 'assignedTo'])->latest();

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

        return view('admin.applications.index', compact('applications', 'assessors'));
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

        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:draft,submitted,under_review,additional_info_required,approved,declined,withdrawn'
            ],
            'status_note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $application->status;
        $newStatus = $validated['status'];

        if ($oldStatus === 'approved' && $newStatus !== 'approved') {
            return back()->with('error', 'Cannot change status of an approved application.');
        }

        // ✅ FIX: Error message previously said "approved" — now correctly says "declined"
        if ($oldStatus === 'declined' && $newStatus !== 'declined') {
            return back()->with('error', 'Cannot change status of a declined application.');
        }

        DB::beginTransaction();
        try {
            $application->update(['status' => $newStatus]);

            if (!empty($validated['status_note'])) {
                $application->comments()->create([
                    'user_id'           => auth()->id(),
                    'comment'           => 'Status changed to ' . ucwords(str_replace('_', ' ', $newStatus)) . ': ' . $validated['status_note'],
                    'is_internal'       => true,
                    'is_client_visible' => false,
                    'commenter_ip'      => $request->ip(),
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
            Log::error('Failed to update status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update status. Please try again.');
        }

        $this->sendStatusChangeNotifications($application, $oldStatus, $newStatus);

        return back()->with('success', 'Application status updated successfully.');
    }

    /**
     * ✅ FIX: Inject MessagingService so the WhatsApp→SMS switch happens in one place
     */
    protected function sendStatusChangeNotifications(Application $application, string $oldStatus, string $newStatus): void
    {
        $messaging = app(MessagingService::class);
        $hasPhone  = $application->personalDetails?->mobile_phone;

        try {
            switch ($newStatus) {
                case 'under_review':
                    $application->user->notify(new \App\Notifications\Application\ApplicationUnderReview($application));
                    if ($hasPhone) {
                        $messaging->send(
                            $application->personalDetails->mobile_phone,
                            "Your loan application #{$application->application_number} is now under review. - Loan Team",
                            $application
                        );
                    }
                    break;

                case 'approved':
                    $application->user->notify(new \App\Notifications\Application\ApplicationApproved($application));
                    if ($hasPhone) {
                        $messaging->send(
                            $application->personalDetails->mobile_phone,
                            "Congratulations! Your loan application #{$application->application_number} has been APPROVED! - Loan Team",
                            $application
                        );
                    }
                    break;

                case 'declined':
                    $application->user->notify(new \App\Notifications\Application\ApplicationDeclined($application, 'Application declined after review'));
                    if ($hasPhone) {
                        $messaging->send(
                            $application->personalDetails->mobile_phone,
                            "Regarding your loan application #{$application->application_number} - we're unable to proceed. Check email for details. - Loan Team",
                            $application
                        );
                    }
                    break;

                case 'additional_info_required':
                    $application->user->notify(new \App\Notifications\Application\ApplicationAdditionalInfoRequired($application));
                    if ($hasPhone) {
                        $messaging->send(
                            $application->personalDetails->mobile_phone,
                            "We need additional information for your application #{$application->application_number}. Please log in. - Loan Team",
                            $application
                        );
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send status change notifications: ' . $e->getMessage());
        }
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

        $exportDate = now();
        $exportedBy = auth()->user();

        $pdf = Pdf::loadView('admin.applications.pdf', compact('application', 'exportDate', 'exportedBy'));

        return $pdf->download("application-{$application->application_number}.pdf");
    }

    public function returnToClient(Request $request, Application $application): RedirectResponse
    {
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

            $application->comments()->create([
                'user_id'           => auth()->id(),
                'comment'           => 'Application returned for amendments: ' . $validated['return_reason'],
                'is_internal'       => false,
                'is_client_visible' => true,
                'commenter_ip'      => $request->ip(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to return application: ' . $e->getMessage());
            return back()->with('error', 'Failed to return application. Please try again.');
        }

        $this->handleReturnNotifications($application, $validated['return_reason'], $request->boolean('notify_sms'));

        return back()->with('success', 'Application returned to client successfully.');
    }

    protected function handleReturnNotifications(Application $application, string $reason, bool $sendSms): void
    {
        try {
            $application->user->notify(new \App\Notifications\Application\ApplicationReturned($application));
        } catch (\Exception $e) {
            Log::error('Failed to send return email: ' . $e->getMessage());
        }

        // ✅ FIX: Use MessagingService instead of TwilioService directly
        if ($sendSms && $application->personalDetails?->mobile_phone) {
            try {
                app(MessagingService::class)->send(
                    $application->personalDetails->mobile_phone,
                    "Your application #{$application->application_number} has been returned. Reason: {$reason}.",
                    $application
                );
            } catch (\Exception $e) {
                Log::error('Failed to send return SMS: ' . $e->getMessage());
            }
        }
    }
}
