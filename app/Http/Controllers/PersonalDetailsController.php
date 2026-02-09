<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\PersonalDetail;
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
                    ->ignore($application->personalDetails?->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('personal_details', 'email')
                    ->ignore($application->personalDetails?->id)
            ],
            'marital_status' => 'required|in:single,married,divorced,widowed,defacto',
            'number_of_dependants' => 'required|integer|min:0',
            'spouse_name' => 'nullable|required_if:marital_status,married|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
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

        return back()->with('success', $message);
    }
}
