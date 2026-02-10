<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\PersonalDetail;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        // This is now publicly accessible - no authentication required
        return view('applications.create');
    }

    public function store(Request $request)
    {
        $isAuthenticated = auth()->check();

        $rules = [
            // Application
            'loan_amount' => 'required|numeric|min:1000',
            'loan_purpose' => 'required|string',
            'loan_purpose_details' => 'nullable|string',
            'term_months' => 'required|integer|min:1|max:360',
            'security_type' => 'nullable|string',

            // Consent
            'privacy_consent' => 'required|accepted',
            'terms_consent' => 'required|accepted',
        ];

        // Extra rules for guests
        if (! $isAuthenticated) {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            /** @var \App\Models\User $user */
            if ($isAuthenticated) {
                $user = auth()->user();
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                $user->assignRole('client');
                Auth::login($user);
            }

            $application = $user->applications()->create([
                'loan_amount' => $validated['loan_amount'],
                'loan_purpose' => $validated['loan_purpose'],
                'loan_purpose_details' => $validated['loan_purpose_details'] ?? null,
                'term_months' => $validated['term_months'],
                'security_type' => $validated['security_type'] ?? null,
                'submission_ip' => $request->ip(),
            ]);

            // Declarations
            $application->declarations()->createMany([
                [
                    'declaration_type' => 'privacy',
                    'declaration_text' => 'I consent to my personal information being collected, used, and disclosed in accordance with the Privacy Policy.',
                    'is_agreed' => true,
                    'agreed_at' => now(),
                    'agreement_ip' => $request->ip(),
                ],
                [
                    'declaration_type' => 'terms',
                    'declaration_text' => 'I have read, understood, and agree to the Terms and Conditions.',
                    'is_agreed' => true,
                    'agreed_at' => now(),
                    'agreement_ip' => $request->ip(),
                ],
            ]);

            ActivityLog::logActivity(
                'created',
                'Application created',
                $application
            );

            DB::commit();

            return redirect()
                ->route('applications.edit', $application)
                ->with('success', 'Your application has been created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Application creation failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create your application. Please try again.');
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
