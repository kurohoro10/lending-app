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
                // User name fields (update User table only)
                'first_name'    => 'required|string|max:255',
                'middle_name'   => 'nullable|string|max:255',
                'last_name'     => 'required|string|max:255',
                'name_extension' => 'nullable|string|max:50',
                'email'         => [
                    'required',
                    'email:rfc,dns',
                    Rule::unique('users', 'email')
                        ->ignore($application->user_id),
                ],
                
                // PersonalDetail fields
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
                'visa_type'            => 'nullable|in:student_visa,work_visa,refugee_visa,working_holiday_visa',
                'contact_role'         => 'nullable|in:director,sole_trader,partner,other',
                'agree_as_guarantor'   => 'nullable|boolean',

                // Conditional — spouse fields only when married
                'spouse_name'   => 'nullable|required_if:marital_status,married|string|max:255',
                'spouse_income' => 'nullable|required_if:marital_status,married|numeric|min:0',
            ], [
                'first_name.required'         => 'First name is required.',
                'last_name.required'          => 'Last name is required.',
                'email.required'              => 'Email is required.',
                'email.unique'                => 'This email is already in use.',
                'date_of_birth.before_or_equal' => 'You must be at least 18 years old to apply.',
                'spouse_name.required_if'     => 'Spouse name is required when married.',
                'spouse_income.required_if'   => 'Spouse income is required when married.',
            ]);

            // Update User with name and email changes
            $application->user->update([
                'first_name'    => $validated['first_name'],
                'middle_name'   => $validated['middle_name'],
                'last_name'     => $validated['last_name'],
                'name_extension' => $validated['name_extension'],
                'email'         => $validated['email'],
            ]);

            // Prepare PersonalDetail data (exclude name/email fields)
            $personalDetailData = [
                'application_id'      => $application->id,
                'user_id'             => $application->user_id,
                'mobile_phone'        => $validated['mobile_phone'],
                'date_of_birth'       => $validated['date_of_birth'],
                'gender'              => $validated['gender'],
                'marital_status'      => $validated['marital_status'],
                'number_of_dependants' => $validated['number_of_dependants'],
                'citizenship_status'  => $validated['citizenship_status'],
                'visa_type'           => $validated['visa_type'],
                'contact_role'        => $validated['contact_role'],
                'spouse_name'         => $validated['spouse_name'],
                'spouse_income'       => $validated['spouse_income'],
                'agree_as_guarantor'  => $validated['agree_as_guarantor'] ?? false,
            ];

            // Store old values for activity log
            if ($application->personalDetails) {
                $oldValues = $application->personalDetails->toArray();
                $application->personalDetails->update($personalDetailData);
                $message = 'Personal details updated successfully.';
            } else {
                $oldValues = null;
                $application->personalDetails()->create($personalDetailData);
                $message = 'Personal details saved successfully.';
            }

            // Log activity
            ActivityLog::logActivity(
                $oldValues ? 'updated' : 'created',
                $oldValues ? 'Personal details updated' : 'Personal details added',
                $application->fresh()->personalDetails,
                $oldValues,
                $personalDetailData
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
                    'errors'  => $e->errors(),
                ], 422);
            }

            throw $e;

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
