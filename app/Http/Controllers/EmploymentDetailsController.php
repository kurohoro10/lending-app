<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\EmploymentDetail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class EmploymentDetailsController extends Controller
{
    public function store(Request $request, Application $application)
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

        $employment = $application->employmentDetails()->create($validated);

        if ($employment->employment_start_date) {
            $employment->calculateEmploymentLength();
        }

        ActivityLog::logActivity(
            'created',
            'Added employment details',
            $employment,
            null,
            $validated
        );

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

        ActivityLog::logActivity(
            'updated',
            'Updated employment details',
            $employmentDetail,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Employment details updated successfully.');
    }

    public function destroy(Application $application, EmploymentDetail $employmentDetail)
    {
        $this->authorize('update', $application);

        $employmentDetail->delete();

        ActivityLog::logActivity(
            'deleted',
            'Deleted employment details',
            $application
        );

        return back()->with('success', 'Employment details deleted successfully.');
    }
}
