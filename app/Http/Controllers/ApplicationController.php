<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\PersonalDetail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = auth()->user()->applications()
            ->with(['personalDetails'])
            ->latest()
            ->paginate(10);

        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        return view('applications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_amount' => 'required|numeric|min:1000',
            'loan_purpose' => 'required|string',
            'loan_purpose_details' => 'nullable|string',
            'term_months' => 'required|integer|min:1|max:360',
            'security_type' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $application = auth()->user()->applications()->create(array_merge(
                $validated,
                ['submission_ip' => $request->ip()]
            ));

            ActivityLog::logActivity(
                'created',
                'Application created',
                $application
            );

            DB::commit();

            return redirect()
                ->route('applications.edit', $application)
                ->with('success', 'Application created successfully. Please complete your details.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create application. Please try again.');
        }
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);

        $application->load([
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
            'documents',
            'questions.askedBy',
            'declarations',
        ]);

        return view('applications.show', compact('application'));
    }

    public function edit(Application $application)
    {
        $this->authorize('update', $application);

        if (!$application->isEditable()) {
            return redirect()
                ->route('applications.show', $application)
                ->with('error', 'This application cannot be edited in its current status.');
        }

        $application->load([
            'personalDetails',
            'residentialAddresses',
            'employmentDetails',
            'livingExpenses',
        ]);

        return view('applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        if (!$application->isEditable()) {
            return back()->with('error', 'This application cannot be edited.');
        }

        $validated = $request->validate([
            'loan_amount' => 'required|numeric|min:1000',
            'loan_purpose' => 'required|string',
            'loan_purpose_details' => 'nullable|string',
            'term_months' => 'required|integer|min:1|max:360',
            'security_type' => 'nullable|string',
        ]);

        $oldValues = $application->only(array_keys($validated));
        $application->update($validated);

        ActivityLog::logActivity(
            'updated',
            'Application details updated',
            $application,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Application updated successfully.');
    }

    public function submit(Application $application)
    {
        $this->authorize('update', $application);

        if (!$application->canBeSubmitted()) {
            return back()->with('error', 'Please complete all required sections before submitting.');
        }

        DB::beginTransaction();
        try {
            $application->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submission_ip' => request()->ip(),
            ]);

            ActivityLog::logActivity(
                'submitted',
                'Application submitted for review',
                $application
            );

            // TODO: Send email notification to admin
            // TODO: Send confirmation email to client

            DB::commit();

            return redirect()
                ->route('applications.show', $application)
                ->with('success', 'Application submitted successfully. You will receive a confirmation email shortly.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit application. Please try again.');
        }
    }

    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        if ($application->status !== 'draft') {
            return back()->with('error', 'Only draft applications can be deleted.');
        }

        $application->delete();

        ActivityLog::logActivity(
            'deleted',
            'Application deleted',
            $application
        );

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application deleted successfully.');
    }
}
