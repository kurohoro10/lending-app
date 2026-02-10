<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with(['user', 'personalDetails', 'assignedTo'])
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhereHas('personalDetails', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $applications = $query->paginate(20);
        $assessors = User::role('assessor')->get();

        return view('admin.applications.index', compact('applications', 'assessors'));
    }

    public function show(Application $application)
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

    public function updateStatus(Request $request, Application $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,under_review,additional_info_required,approved,declined,withdrawn',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $application->status;
        $application->update(['status' => $validated['status']]);

        \App\Models\ActivityLog::logActivity(
            'status_changed',
            "Status changed from {$oldStatus} to {$validated['status']}",
            $application,
            ['status' => $oldStatus],
            ['status' => $validated['status']]
        );

        // TODO: Send notification to client

        return back()->with('success', 'Application status updated successfully.');
    }

    public function assign(Request $request, Application $application)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $application->update($validated);

        \App\Models\ActivityLog::logActivity(
            'assigned',
            'Application assigned to assessor',
            $application
        );

        // TODO: Send notification to assigned assessor

        return back()->with('success', 'Application assigned successfully.');
    }

    public function exportPdf(Application $application)
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
}
