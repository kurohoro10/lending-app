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
                    'required', 'string', 'max:20',
                    Rule::unique('personal_details', 'mobile_phone')
                        ->where(fn ($q) => $q->where('application_id', $application->id))
                        ->ignore($application->personalDetails?->id),
                ],
                'date_of_birth' => [
                    'required', 'date',
                    'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                ],
                'gender'               => 'nullable|in:male,female,other,prefer_not_to_say',
                'marital_status'       => 'required|in:single,married,divorced,widowed,defacto',
                'number_of_dependants' => 'required|integer|min:0',
                'citizenship_status'   => 'required|in:australian_citizen,permanent_resident,temporary_resident,nz_citizen',
                'contact_role'         => 'nullable|in:director,sole_trader,partner,other',

                // Conditional — spouse fields only when married
                'spouse_name'   => 'nullable|required_if:marital_status,married|string|max:255',
                'spouse_income' => 'nullable|required_if:marital_status,married|numeric|min:0',
            ], [
                'date_of_birth.before_or_equal' => 'You must be at least 18 years old to apply.',
                'spouse_name.required_if'       => 'Spouse name is required when married.',
                'spouse_income.required_if'     => 'Spouse income is required when married.',
            ]);

            $validated['application_id'] = $application->id;
            $validated['user_id']        = $application->user_id;

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
                $application->fresh()->personalDetails,
                $oldValues,
                $validated
            );

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success'                 => true,
                    'message'                 => $message,
                    'data'                    => $application->fresh()->personalDetails,
                    'type'                    => 'personal',
                    'trigger_progress_update' => true,
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(), // 🔥 ALL validation errors
                ], 422);
            }

            throw $e; // Let Laravel handle redirect with errors normally

        } catch (\Throwable $e) {

            \Log::error('Failed to save personal details', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
