<?php

/**
 * @file    app/Http/Controllers/ApplicationController.php
 * @package App\Http\Controllers
 *
 * Manages the client-facing lifecycle of loan applications within the
 * commercial loan application system.
 *
 * Responsibilities:
 *  - Listing the authenticated user's applications with pending question counts
 *  - Rendering the application creation form with optional calculator pre-fill
 *  - Creating a new application, optionally registering a new user in the same flow
 *  - Displaying a read-only application view with all related sections loaded
 *  - Editing and updating application loan details with change-tracking notifications
 *  - Submitting a completed application with a final declaration and signature
 *  - Deleting draft applications
 *  - Marking the CreditSense bank statement step as complete via AJAX
 *
 * Guest creation flow (store):
 *  - When the user is unauthenticated, a new User account and `client` role are
 *    created within the same DB transaction as the application.
 *  - The user is logged in automatically on success.
 *  - A WelcomeNewUser notification is dispatched; ApplicationCreated is used for
 *    existing authenticated users.
 *
 * Notification strategy:
 *  - All SMS delivery is routed through MessagingService to allow provider switching.
 *  - Notification failures are caught and logged without rolling back the main action.
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers;

use App\Actions\Application\SubmitApplication;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\User;
use App\Notifications\Application\ApplicationCreated;
use App\Notifications\Application\ApplicationUpdated;
use App\Notifications\NewUser\WelcomeNewUser;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Declaration records created automatically on application submission.
     *
     * @var string[]
     */
    private const DECLARATION_TYPES = ['privacy', 'terms'];

    /**
     * Declaration text stored for each consent type at creation time.
     *
     * @var array<string, string>
     */
    private const DECLARATION_TEXTS = [
        'privacy' => 'I consent to my personal information being collected, used, and disclosed in accordance with the Privacy Policy.',
        'terms'   => 'I have read, understood, and agree to the Terms and Conditions.',
    ];

    // =========================================================================
    // Listing
    // =========================================================================

    /**
     * Display the authenticated user's application list.
     *
     * Eager-loads personal details and annotates each application with a count
     * of pending (unanswered) questions. Also computes a total pending question
     * count for the dashboard badge.
     *
     * @return View  The `applications.index` view.
     */
    public function index(): View
    {
        $user = auth()->user();

        $applications = $user->applications()
            ->with(['personalDetails'])
            ->withCount(['questions' => fn ($q) => $q->where('status', 'pending')])
            ->latest()
            ->paginate(10);

        $totalPendingQuestions = $user->applications()
            ->join('questions', 'applications.id', '=', 'questions.application_id')
            ->where('questions.status', 'pending')
            ->count();

        return view('applications.index', compact('applications', 'totalPendingQuestions'));
    }

    // =========================================================================
    // Creation Form
    // =========================================================================

    /**
     * Display the application creation form with optional calculator pre-fill.
     *
     * Accepts `amount`, `term`, and `rate` query parameters so users arriving
     * from the loan calculator landing page see their values pre-populated.
     *
     * @param  Request  $request  Incoming HTTP request; may include calculator query params.
     * @return View               The `applications.create` view.
     *
     * @queryParam numeric amount  Loan amount to pre-fill (default: 100000).
     * @queryParam int    term     Loan term in months to pre-fill (default: 60).
     * @queryParam float  rate     Interest rate to pre-fill (default: 8.5).
     */
    public function create(Request $request): View
    {
        $calculatorValues = [
            'loan_amount'   => $request->query('amount', 100000),
            'term_months'   => $request->query('term', 60),
            'interest_rate' => $request->query('rate', 8.5),
        ];

        return view('applications.create', compact('calculatorValues'));
    }

    // =========================================================================
    // Store (Create Application)
    // =========================================================================

    /**
     * Create a new loan application, registering a new user if the guest is unauthenticated.
     *
     * For unauthenticated guests, a User record is created within the same DB
     * transaction as the application. On success, the new user is logged in
     * automatically and a welcome notification is dispatched.
     *
     * The privacy and terms declarations are persisted as part of the transaction.
     * Notification failures are caught and logged without rolling back.
     *
     * @param  Request  $request  Incoming HTTP request with loan and optional user fields.
     * @return RedirectResponse   Redirect to the application edit page on success.
     *
     * @bodyParam numeric loan_amount          required  Requested loan amount (min 1000).
     * @bodyParam string  loan_purpose         required  Purpose of the loan.
     * @bodyParam string  loan_purpose_details nullable  Additional loan purpose detail.
     * @bodyParam int     term_months          required  Loan term in months (1–360).
     * @bodyParam string  security_type        nullable  Type of security offered.
     * @bodyParam boolean privacy_consent      required  Must be accepted.
     * @bodyParam boolean terms_consent        required  Must be accepted.
     * @bodyParam string  first_name           required (guest only) Applicant first name.
     * @bodyParam string  middle_name          nullable (guest only) Applicant middle name.
     * @bodyParam string  last_name            required (guest only) Applicant last name.
     * @bodyParam string  name_extension       nullable (guest only) e.g. Jr., Sr.
     * @bodyParam string  email                required (guest only) Unique email address.
     * @bodyParam string  password             required (guest only) Min 8 chars, confirmed.
     */
    public function store(Request $request): RedirectResponse
    {
        $isAuthenticated = auth()->check();

        $validated = $this->validateApplicationStore($request, $isAuthenticated);

        DB::beginTransaction();
        try {
            $user = $isAuthenticated
                ? auth()->user()
                : $this->registerNewUser($validated);

            $application = $this->createApplication($request, $user, $validated);

            $this->createConsentDeclarations($request, $application);

            ActivityLog::logActivity(
                'created',
                $isAuthenticated
                    ? 'Application created by authenticated user'
                    : 'Application created with new account registration',
                $application
            );

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Application creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create your application. Please try again.');
        }

        if (! $isAuthenticated) {
            Auth::login($user);
        }

        $this->sendCreationNotifications($user, $application, $isAuthenticated);

        $message = $isAuthenticated
            ? 'Your application has been started! Please complete your details to continue.'
            : 'Your account has been created and your application has been started! Please complete your details to continue.';

        return redirect()
            ->route('applications.edit', $application)
            ->with('success', $message);
    }

    // =========================================================================
    // Display
    // =========================================================================

    /**
     * Display a read-only view of a loan application.
     *
     * Eager-loads all sections required by the show view in a single query batch.
     *
     * @param  Application  $application  The bound application model instance.
     * @return View                       The `applications.show` view.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `view` policy.
     */
    public function show(Application $application): View
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

    // =========================================================================
    // Edit Form
    // =========================================================================

    /**
     * Display the application edit form.
     *
     * Redirects to the show page with an error if the application is not in an
     * editable status, preventing changes to submitted or locked applications.
     *
     * @param  Application  $application  The bound application model instance.
     * @return View|RedirectResponse      The edit view, or a redirect if not editable.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     */
    public function edit(Application $application): View|RedirectResponse
    {
        $this->authorize('update', $application);

        if (! $application->isEditable()) {
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

    // =========================================================================
    // Update
    // =========================================================================

    /**
     * Update the loan details of an existing application.
     *
     * Tracks field-level changes and dispatches an email and SMS notification
     * only when actual changes are detected. Notification failures are caught
     * and logged without rolling back the update.
     *
     * @param  Request      $request      Incoming HTTP request with updated loan fields.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success flash message.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @bodyParam numeric loan_amount          required  Updated loan amount (min 1000).
     * @bodyParam string  loan_purpose         required  Updated loan purpose.
     * @bodyParam string  loan_purpose_details nullable  Updated loan purpose detail.
     * @bodyParam int     term_months          required  Updated term in months (1–360).
     * @bodyParam string  security_type        nullable  Updated security type.
     */
    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        if (! $application->isEditable()) {
            return back()->with('error', 'This application cannot be edited.');
        }

        $validated = $this->validateApplicationUpdate($request);

        $oldValues = $application->only(array_keys($validated));
        $changes   = $this->detectChanges($oldValues, $validated);

        $application->update($validated);

        ActivityLog::logActivity('updated', 'Application details updated', $application, $oldValues, $validated);

        if (! empty($changes)) {
            $this->sendUpdateNotifications($application, $changes);
        }

        return back()->with('success', 'Application updated successfully.');
    }

    // =========================================================================
    // Submission
    // =========================================================================

    /**
     * Submit a completed application with a final declaration and signature.
     *
     * Creates a `final_submission` declaration record within a DB transaction.
     * Delegates the submission action (status change, notifications) to
     * `SubmitApplication`. Both phases have independent try/catch blocks so
     * a signature failure rolls back cleanly without executing the action.
     *
     * @param  Application       $application  The bound application model instance.
     * @param  SubmitApplication $action       The submission action handler.
     * @return RedirectResponse                Redirect to show page on success.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @bodyParam string  signature           required  Base64 or SVG signature data.
     * @bodyParam boolean signature_agreement required  Must be accepted.
     * @bodyParam string  signature_type      nullable  Signature method (default: `drawn`).
     * @bodyParam string  signatory_position  nullable  Role or position of the signatory.
     */
    public function submit(Application $application, SubmitApplication $action): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = request()->validate([
            'signature'           => ['required', 'string'],
            'signature_agreement' => ['accepted'],
            'signature_type'      => ['nullable', 'string'],
            'signatory_position'  => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $this->createFinalDeclaration($application, $validated);

            if (! $application->canBeSubmitted()) {
                DB::rollBack();
                return back()->with('error', 'Please complete all required sections before submitting.');
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create signature: ' . $e->getMessage());
            return back()->with('error', 'Failed to process signature. Please try again.');
        }

        try {
            $action->handle($application, $validated);

            return redirect()
                ->route('applications.show', $application)
                ->with('success', 'Application submitted successfully.');

        } catch (\Exception $e) {
            Log::error('Application submission failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit application. Please try again.');
        }
    }

    // =========================================================================
    // Deletion
    // =========================================================================

    /**
     * Delete a draft application.
     *
     * Only applications in `draft` status may be deleted. Redirects to the
     * index with an error for any other status.
     *
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect to the index with success or error flash.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `delete` policy.
     */
    public function destroy(Application $application): RedirectResponse
    {
        $this->authorize('delete', $application);

        if ($application->status !== 'draft') {
            return back()->with('error', 'Only draft applications can be deleted.');
        }

        $application->delete();

        ActivityLog::logActivity('deleted', 'Application deleted', $application);

        return redirect()
            ->route('applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    // =========================================================================
    // Bank Statement Completion (AJAX)
    // =========================================================================

    /**
     * Mark the CreditSense bank statement step as complete.
     *
     * Called via AJAX from the CreditSense JS callback when response code `99`
     * (connection established) or `100` (completed) is received. Sets
     * `credit_sense_completed_at` so the progress bar can update and
     * `canBeSubmitted()` can pass.
     *
     * Idempotent — safe to call multiple times; subsequent calls return success
     * without modifying the record or writing an additional activity log entry.
     *
     * Note: The actual bank data arrives separately via the CreditSense webhook.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success confirmation.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the user lacks `update` policy.
     *
     * @response 200 { "success": true, "message": "Bank statements marked as connected." }
     */
    public function completeBankStatements(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (! $application->credit_sense_completed_at) {
            $application->update([
                'credit_sense_completed_at' => now(),
            ]);

            ActivityLog::logActivity(
                'bank_statements_connected',
                'Client completed CreditSense bank statement connection',
                $application
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank statements marked as connected.',
        ]);
    }

    // =========================================================================
    // Private Helpers — Validation
    // =========================================================================

    /**
     * Validate the application creation request payload.
     *
     * Merges guest-only user registration rules when the user is not authenticated.
     *
     * @param  Request  $request          The incoming HTTP request.
     * @param  bool     $isAuthenticated  Whether the user is currently logged in.
     * @return array                      Validated loan and (conditionally) user fields.
     */
    private function validateApplicationStore(Request $request, bool $isAuthenticated): array
    {
        $rules = [
            'loan_amount'          => ['required', 'numeric', 'min:1000'],
            'loan_purpose'         => ['required', 'string'],
            'loan_purpose_details' => ['nullable', 'string'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:360'],
            'security_type'        => ['nullable', 'string'],
            'privacy_consent'      => ['required', 'accepted'],
            'terms_consent'        => ['required', 'accepted'],
        ];

        if (! $isAuthenticated) {
            $rules = array_merge($rules, [
                'first_name'     => ['required', 'string', 'max:100'],
                'middle_name'    => ['nullable', 'string', 'max:100'],
                'last_name'      => ['required', 'string', 'max:100'],
                'name_extension' => ['nullable', 'string', 'max:20'],
                'email'          => ['required', 'email', 'unique:users,email'],
                'password'       => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        }

        return $request->validate($rules);
    }

    /**
     * Validate the application update request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated loan detail fields.
     */
    private function validateApplicationUpdate(Request $request): array
    {
        return $request->validate([
            'loan_amount'          => ['required', 'numeric', 'min:1000'],
            'loan_purpose'         => ['required', 'string'],
            'loan_purpose_details' => ['nullable', 'string'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:360'],
            'security_type'        => ['nullable', 'string'],
        ]);
    }

    // =========================================================================
    // Private Helpers — User Registration
    // =========================================================================

    /**
     * Register a new User from validated guest registration fields.
     *
     * Assigns the `client` role after creation. Must be called within an open
     * DB transaction so that the user record rolls back if application creation
     * subsequently fails.
     *
     * @param  array  $validated  Validated fields including name, email, and password.
     * @return User               The newly created and role-assigned user model.
     */
    private function registerNewUser(array $validated): User
    {
        $user = User::create([
            'first_name'     => $validated['first_name'],
            'middle_name'    => $validated['middle_name'] ?? null,
            'last_name'      => $validated['last_name'],
            'name_extension' => $validated['name_extension'] ?? null,
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
        ]);

        $user->assignRole('client');

        return $user;
    }

    // =========================================================================
    // Private Helpers — Persistence
    // =========================================================================

    /**
     * Create the Application record for the given user.
     *
     * @param  Request  $request    The HTTP request (used for submission IP).
     * @param  User     $user       The application owner.
     * @param  array    $validated  Validated loan fields.
     * @return Application          The newly created application model.
     */
    private function createApplication(Request $request, User $user, array $validated): Application
    {
        return $user->applications()->create([
            'loan_amount'          => $validated['loan_amount'],
            'loan_purpose'         => $validated['loan_purpose'],
            'loan_purpose_details' => $validated['loan_purpose_details'] ?? null,
            'term_months'          => $validated['term_months'],
            'security_type'        => $validated['security_type'] ?? null,
            'submission_ip'        => $request->ip(),
        ]);
    }

    /**
     * Persist the privacy and terms consent declarations for a new application.
     *
     * Each declaration records the agreed timestamp and the client's IP address
     * at the time of consent.
     *
     * @param  Request      $request      The HTTP request (used for agreement IP).
     * @param  Application  $application  The application to attach declarations to.
     * @return void
     */
    private function createConsentDeclarations(Request $request, Application $application): void
    {
        foreach (self::DECLARATION_TYPES as $type) {
            $application->declarations()->create([
                'declaration_type' => $type,
                'declaration_text' => self::DECLARATION_TEXTS[$type],
                'is_agreed'        => true,
                'agreed_at'        => now(),
                'agreement_ip'     => $request->ip(),
            ]);
        }
    }

    /**
     * Create the final submission declaration with the captured signature.
     *
     * @param  Application  $application  The application being submitted.
     * @param  array        $validated    Validated signature payload.
     * @return void
     */
    private function createFinalDeclaration(Application $application, array $validated): void
    {
        $application->declarations()->create([
            'declaration_type'    => 'final_submission',
            'declaration_text'    => 'I declare that all information provided in this application is true and accurate to the best of my knowledge. I understand that providing false or misleading information may result in rejection of this application or legal action.',
            'is_agreed'           => true,
            'agreed_at'           => now(),
            'agreement_ip'        => request()->ip(),
            'signature_data'      => $validated['signature'],
            'signature_type'      => $validated['signature_type'] ?? 'drawn',
            'signatory_name'      => auth()->user()->name,
            'signatory_position'  => $validated['signatory_position'] ?? null,
            'signature_timestamp' => now(),
        ]);
    }

    /**
     * Detect which validated fields have changed from their stored values.
     *
     * Uses loose comparison (`!=`) to handle type coercion between stored
     * string values and submitted numeric or boolean values.
     *
     * @param  array  $oldValues  The application's current field values.
     * @param  array  $validated  The incoming validated field values.
     * @return array              Associative array of changed key → new value pairs.
     */
    private function detectChanges(array $oldValues, array $validated): array
    {
        $changes = [];

        foreach ($validated as $key => $value) {
            if ($oldValues[$key] != $value) {
                $changes[$key] = $value;
            }
        }

        return $changes;
    }

    // =========================================================================
    // Private Helpers — Notifications
    // =========================================================================

    /**
     * Dispatch creation notifications to the user after a new application is stored.
     *
     * New users receive a WelcomeNewUser notification; returning authenticated
     * users receive ApplicationCreated. Both paths optionally send an SMS when
     * a mobile number is available. Failures are caught and logged.
     *
     * @param  User         $user             The application owner.
     * @param  Application  $application      The newly created application.
     * @param  bool         $isAuthenticated  Whether the user was already logged in.
     * @return void
     */
    private function sendCreationNotifications(User $user, Application $application, bool $isAuthenticated): void
    {
        try {
            if (! $isAuthenticated) {
                $user->notify(new WelcomeNewUser($application));

                $this->sendApplicationSms(
                    $application,
                    "Welcome to LoanFlow! Your loan application #{$application->application_number} has been created. Complete your details to submit."
                );
            } else {
                $user->notify(new ApplicationCreated($application));

                $this->sendApplicationSms(
                    $application,
                    "Your loan application #{$application->application_number} has been created successfully."
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications: ' . $e->getMessage());
        }
    }

    /**
     * Dispatch update notifications when application fields have changed.
     *
     * Sends an ApplicationUpdated email and an SMS summarising the changed fields.
     * Failures are caught and logged without rolling back the update.
     *
     * @param  Application  $application  The updated application.
     * @param  array        $changes      Associative array of changed key → new value pairs.
     * @return void
     */
    private function sendUpdateNotifications(Application $application, array $changes): void
    {
        try {
            $application->user->notify(new ApplicationUpdated($application, $changes));

            $changesList = implode(', ', array_keys($changes));

            $this->sendApplicationSms(
                $application,
                "Your loan application #{$application->application_number} has been updated. Changes: {$changesList}"
            );
        } catch (\Exception $e) {
            Log::error('Failed to send update notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send an SMS notification for an application event via MessagingService.
     *
     * Silently returns when no mobile phone number is recorded. Failures are
     * caught and logged without surfacing an HTTP error.
     *
     * @param  Application  $application  The application providing the recipient number.
     * @param  string       $message      The SMS body text to send.
     * @return void
     */
    protected function sendApplicationSms(Application $application, string $message): void
    {
        if (! $application->personalDetails?->mobile_phone) {
            return;
        }

        try {
            app(MessagingService::class)->send(
                $application->personalDetails->mobile_phone,
                $message,
                $application
            );
        } catch (\Exception $e) {
            Log::error('Failed to queue SMS: ' . $e->getMessage());
        }
    }
}