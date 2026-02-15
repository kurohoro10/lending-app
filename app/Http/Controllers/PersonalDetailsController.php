<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonalDetailsController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile_phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('personal_details', 'mobile_phone')
                    ->where(fn ($query) =>
                        $query->where('application_id', $application->id)
                    )
                    ->ignore($application->personalDetails?->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('personal_details', 'email')
                    ->where(fn ($query) =>
                        $query->where('application_id', $application->id)
                    )
                    ->ignore($application->personalDetails?->id)
            ],
            'marital_status' => 'required|in:single,married,divorced,widowed,defacto',
            'number_of_dependants' => 'required|integer|min:0',
            'spouse_name' => 'nullable|required_if:marital_status,married|string|max:255',
            'date_of_birth' => [
                'required', 'date',
                'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            ],
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'citizenship_status' => 'required|in:australian_citizen,permanent_resident,temporary_resident,nz_citizen',
        ], [
            // Custom error message for better UX/Accessibility
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to apply for this loan.',
        ]);

        $validated['application_id'] = $application->id;

        if ($application->personalDetails) {
            $oldValues = $application->personalDetails->toArray();
            $application->personalDetails->update($validated);
            $message = 'Personal details updated successfully.';
        } else {
            $oldValues = null;
            $application->personalDetails()->create($validated);
            $message = 'Personal details saved successfully.';
        }

        ActivityLog::logActivity(
            $oldValues ? 'updated' : 'created',
            $oldValues ? 'Personal details updated' : 'Personal details added',
            $application->personalDetails,
            $oldValues,
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $application->personalDetails
            ], 200);
        }

        // Traditional form submission redirect
        return back()->with('success', $message);
    }
}
