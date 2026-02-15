<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\EmploymentDetail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Rules\LegalWorkingAge;

class EmploymentDetailsController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        if (!$application->personalDetails || !$application->personalDetails->date_of_birth) {
            $errorMessage = 'Please complete Personal Details (Date of Birth) first.';

            // Check if the request expects JSON (AJAX request)
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employment details added successfully.',
                    'employment' => $employment,
                    'type' => 'employment',
                    'trigger_progress_update' => true
                ], 201);
            }

            return back()->withErrors([
                'employment_start_date' => $errorMessage
            ]);
        }

        $validated = $request->validate([
            'employment_type' => 'required|in:payg,self_employed,company_director,contract,casual,retired,unemployed',
            'employer_business_name' => 'nullable|required_unless:employment_type,retired,unemployed|string|max:255',
            'abn' => 'nullable|string|max:20',
            'employment_role' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'employment_start_date' => [
                'required',
                'date',
                new LegalWorkingAge($application->personalDetails->date_of_birth),
            ],
            'base_income' => 'required|numeric|min:0',
            'additional_income' => 'nullable|numeric|min:0',
            'income_frequency' => 'required|in:weekly,fortnightly,monthly,annual',
            'employer_phone' => 'nullable|string|max:20',
            'employer_address' => 'nullable|string',
        ]);

        $employment = $application->employmentDetails()->create($validated);

        if ($employment->employment_start_date) {
            $employment->calculateEmploymentLength();
        }

        // Calculate annual income for response
        $employment->load('application');
        $employment->annual_income = $employment->getAnnualIncome();

        ActivityLog::logActivity(
            'created',
            'Added employment details',
            $employment,
            null,
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employment details added successfully.',
                'employment' => $employment,
                'type' => 'employment',
                'trigger_progress_update' => true
            ], 201);
        }

        return back()->with('success', 'Employment details added successfully.');
    }

    public function update(Request $request, Application $application, EmploymentDetail $employmentDetail)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'employment_type' => 'required|in:payg,self_employed,company_director,contract,casual,retired,unemployed',
            'employer_business_name' => 'nullable|required_unless:employment_type,retired,unemployed|string|max:255',
            'abn' => 'nullable|string|max:20',
            'employment_role' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'employment_start_date' => 'nullable|date|before_or_equal:today',
            'base_income' => 'required|numeric|min:0',
            'additional_income' => 'nullable|numeric|min:0',
            'income_frequency' => 'required|in:weekly,fortnightly,monthly,annual',
            'employer_phone' => 'nullable|string|max:20',
            'employer_address' => 'nullable|string',
        ]);

        $oldValues = $employmentDetail->toArray();
        $employmentDetail->update($validated);

        if ($employmentDetail->employment_start_date) {
            $employmentDetail->calculateEmploymentLength();
        }

        // Calculate annual income for response
        $employmentDetail->annual_income = $employmentDetail->getAnnualIncome();

        ActivityLog::logActivity(
            'updated',
            'Updated employment details',
            $employmentDetail,
            $oldValues,
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employment details updated successfully.',
                'employment' => $employmentDetail,
                'type' => 'employment',
                'trigger_progress_update' => true
            ], 200);
        }

        return back()->with('success', 'Employment details updated successfully.');
    }

    public function destroy(Request $request, Application $application, EmploymentDetail $employmentDetail)
    {
        $this->authorize('update', $application);

        $employmentId = $employmentDetail->id;
        $employmentDetail->delete();

        ActivityLog::logActivity(
            'deleted',
            'Deleted employment details',
            $application
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employment details deleted successfully.',
                'deleted_id' => $employmentId,
                'type' => 'employment',
                'trigger_progress_update' => true
            ], 200);
        }

        return back()->with('success', 'Employment details deleted successfully.');
    }
}
