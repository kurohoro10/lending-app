<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\Application\ApplicationCreated;
use App\Notifications\Application\ApplicationUpdated;
use App\Notifications\Application\ApplicationSubmitted;
use App\Notifications\Admin\NewApplicationSubmittedAdmin;
use App\Notifications\NewUser\WelcomeNewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Services\AutoDeclineService;
use App\Notifications\Application\ApplicationDeclined;
use App\Jobs\SendSMSMessage;
use App\Actions\Application\SubmitApplication;

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

    public function create(Request $request)
    {
        // This is now publicly accessible - no authentication required
        // Accept calculator values from welcome page via query params
        $calculatorValues = [
            'loan_amount' => $request->query('amount', 100000),
            'term_months' => $request->query('term', 60),
            'interest_rate' => $request->query('rate', 8.5),
        ];

        return view('applications.create', compact('calculatorValues'));
    }

    public function store(Request $request)
    {
        // Check if user is already logged in
        $isAuthenticated = auth()->check();

        // Different validation rules for authenticated vs guest users
        $rules = [
            // Application Information
            'loan_amount' => 'required|numeric|min:1000',
            'loan_purpose' => 'required|string',
            'loan_purpose_details' => 'nullable|string',
            'term_months' => 'required|integer|min:1|max:360',
            'security_type' => 'nullable|string',

            // Consent (always required)
            'privacy_consent' => 'required|accepted',
            'terms_consent' => 'required|accepted',
        ];

        // Add account fields validation only for guest users
        if (!$isAuthenticated) {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create or get user
            if ($isAuthenticated) {
                $user = auth()->user();
            } else {
                // Create new user account
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                // Assign client role
                $user->assignRole('client');
            }

            // Create application
            $application = $user->applications()->create([
                'loan_amount' => $validated['loan_amount'],
                'loan_purpose' => $validated['loan_purpose'],
                'loan_purpose_details' => $validated['loan_purpose_details'],
                'term_months' => $validated['term_months'],
                'security_type' => $validated['security_type'],
                'submission_ip' => $request->ip(),
            ]);

            $application->personalDetails()->updateOrCreate(
                ['application_id' => $application->id],
                [
                    'email' => $user->email,
                    'full_name' => $user->name,
                ]
            );

            // Create consent declarations
            $declarations = [
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
            ];

            foreach ($declarations as $declaration) {
                $application->declarations()->create($declaration);
            }

            $activityDescription = $isAuthenticated
                ? 'Application created by authenticated user'
                : 'Application created with new account registration';

            ActivityLog::logActivity(
                'created',
                $activityDescription,
                $application
            );

            DB::commit();

            // Log the user in if they just registered
            if (!$isAuthenticated) {
                Auth::login($user);
            }

            // Send email and SMS notifications
            try {
                $twilio = app(\App\Services\TwilioService::class);

                if (!$isAuthenticated) {
                    // New user - send welcome email
                    $user->notify(new WelcomeNewUser($application));

                    // Send welcome SMS/WhatsApp if phone available
                    if ($application->personalDetails?->mobile_phone) {
                        try {
                            $twilio->sendWhatsApp(
                                $application->personalDetails->mobile_phone,
                                "Welcome to LoanFlow! Your loan application #{$application->application_number} has been created. Complete your details to submit.",
                                $application
                            );
                        } catch (\Exception $e) {
                            \Log::error('Failed to send welcome SMS: ' . $e->getMessage());
                        }
                    }
                } else {
                    // Existing user - send application created notification
                    $user->notify(new ApplicationCreated($application));

                    // Send SMS notification
                    if ($application->personalDetails?->mobile_phone) {
                        try {
                            $twilio->sendWhatsApp(
                                $application->personalDetails->mobile_phone,
                                "Your loan application #{$application->application_number} has been created successfully.",
                                $application
                            );
                        } catch (\Exception $e) {
                            \Log::error('Failed to send creation SMS: ' . $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send notifications: ' . $e->getMessage());
            }

            $message = $isAuthenticated
                ? 'Your application has been started! Please complete your details to continue.'
                : 'Your account has been created and your application has been started! Please complete your details to continue.';

            return redirect()
                ->route('applications.edit', $application)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            \Log::error('Application creation failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create your application. Please try again. Error: ' . $e->getMessage());
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
        $changes = [];

        // Detect what actually changed
        foreach ($validated as $key => $value) {
            if ($oldValues[$key] != $value) {
                $changes[$key] = $value;
            }
        }

        $application->update($validated);

        ActivityLog::logActivity(
            'updated',
            'Application details updated',
            $application,
            $oldValues,
            $validated
        );

        // Send email and SMS notification only if there were actual changes
        if (!empty($changes)) {
            try {
                $application->user->notify(new ApplicationUpdated($application, $changes));

                // Send SMS notification
                if ($application->personalDetails?->mobile_phone) {
                    $changesList = implode(', ', array_keys($changes));
                    try {
                        app(\App\Services\TwilioService::class)->sendWhatsApp(
                            $application->personalDetails->mobile_phone,
                            "Your loan application #{$application->application_number} has been updated. Changes: {$changesList}",
                            $application
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to send update SMS: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send update notifications: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Application updated successfully.');
    }

    public function submit(Application $application, SubmitApplication $action)
    {
        $this->authorize('update', $application);

        // Validate signature first
        $validated = request()->validate([
            'signature' => ['required', 'string'],
            'signature_agreement' => ['accepted'],
            'signature_type' => ['nullable', 'string'],
            'signatory_position' => ['nullable', 'string'],
        ]);

        \Log::info('Signature validated', [
            'has_signature' => !empty($validated['signature']),
            'signature_length' => strlen($validated['signature']),
        ]);

        // Create signature BEFORE canBeSubmitted check
        DB::beginTransaction();
        try {
            $declaration = $application->declarations()->create([
                'declaration_type'   => 'final_submission',
                'declaration_text'   => 'I declare that all information provided in this application is true and accurate to the best of my knowledge. I understand that providing false or misleading information may result in rejection of this application or legal action.',
                'is_agreed'          => true,
                'agreed_at'          => now(),
                'agreement_ip'       => request()->ip(),
                'signature_data'     => $validated['signature'],
                'signature_type'     => $validated['signature_type'] ?? 'typed',
                'signatory_name'     => auth()->user()->name,
                'signatory_position' => $validated['signatory_position'] ?? null,
                'signature_timestamp'=> now(),
            ]);

            \Log::info('Signature created', [
                'declaration_id' => $declaration->id,
            ]);

            // NOW check if can be submitted (signature exists now)
            if (!$application->canBeSubmitted()) {
                DB::rollBack();
                \Log::warning('Submit validation failed after signature creation');
                return back()->with('error', 'Please complete all required sections before submitting.');
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create signature', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to process signature. Please try again.');
        }

        // Now process the submission
        try {
            $action->handle($application, $validated);

            return redirect()
                ->route('applications.show', $application)
                ->with('success', 'Application submitted successfully.');
        } catch (\Exception $e) {
            \Log::error('Application submission failed: ' . $e->getMessage());

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

    /**
     * Send SMS notification for application event
     */
    protected function sendApplicationSMS(Application $application, string $message): void
    {
        if (!$application->personalDetails?->mobile_phone) {
            return;
        }

        try {
            SendSMSMessage::dispatch(
                $application->personalDetails->mobile_phone,
                $message,
                $application->id
            );
        } catch (\Exception $e) {
            \Log::error('Failed to queue SMS: ' . $e->getMessage());
        }
    }
}
