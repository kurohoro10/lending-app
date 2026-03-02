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

        try {
            $validated = $request->validate([
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
                'marital_status' => 'required|in:single,married,divorced,widowed,defacto',
                'number_of_dependants' => 'required|integer|min:0',
                'spouse_name' => 'nullable|required_if:marital_status,married|string|max:255',
                'date_of_birth' => [
                    'required',
                    'date',
                    'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                ],
                'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
                'citizenship_status' => 'required|in:australian_citizen,permanent_resident,temporary_resident,nz_citizen',
            ], [
                // Custom error message for better UX / accessibility
                'date_of_birth.before_or_equal' =>
                    'You must be at least 18 years old to apply for this loan.',
            ]);

            $validated['application_id'] = $application->id;
            $validated['user_id'] = $application->user_id;

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

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $application->personalDetails,
                    'type' => 'personal',
                    'trigger_progress_update' => true,
                ], 200);
            }

            return back()->with('success', $message);

        } catch (\Throwable $e) {
            \Log::error('Failed to save personal details', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);

            $genericMessage =
                'We couldn’t save your personal details at this time. Please try again.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $genericMessage,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $genericMessage);
        }
    }
}
