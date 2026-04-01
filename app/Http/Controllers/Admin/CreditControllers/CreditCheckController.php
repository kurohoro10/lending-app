<?php

/**
 * @file    app/Http/Controllers/Admin/CreditControllers/CreditCheckController.php
 * @package App\Http\Controllers\Admin\CreditControllers
 *
 * Manages the lifecycle of credit reporting and scoring requests within the
 * commercial loan application system.
 *
 * Responsibilities:
 *  - Initiating credit check requests against supported external providers
 *  - Updating credit check records with provider scoring and response data
 *  - Displaying detailed credit check results for admin review
 *
 * Supported providers (validated on request):
 *  - `credit_sense` — Credit Sense open banking / cashflow analysis
 *  - `equifax`      — Equifax credit bureau report
 *  - `experian`     — Experian credit bureau report
 *
 * Pending integration:
 *  - ProcessCreditCheck job (see TODO in request())
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CreditCheck;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditCheckController extends Controller
{
    /**
     * Allowed credit check provider identifiers.
     *
     * Kept as a constant so the validation rule and any downstream provider
     * switch statements share a single source of truth.
     *
     * @var string[]
     */
    private const PROVIDERS = ['credit_sense', 'equifax', 'experian'];

    // =========================================================================
    // Initiation
    // =========================================================================

    /**
     * Initiate a new credit check request for a specific application.
     *
     * Creates a `CreditCheck` record in `pending` status and logs the activity.
     * The record is the trigger point for the queued API job (currently stubbed
     * — see the TODO comment below).
     *
     * @param  Request      $request      Incoming HTTP request containing provider and notes.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with a success flash message.
     *
     * @bodyParam string   provider  required  Provider identifier — one of: credit_sense, equifax, experian.
     * @bodyParam string   notes     nullable  Optional internal notes about this request.
     */
    public function request(Request $request, Application $application): RedirectResponse
    {
        $validated = $this->validateCreditCheckRequest($request);

        $creditCheck = $this->createPendingCreditCheck($application, $validated);

        ActivityLog::logActivity(
            'requested',
            "Requested credit check from {$validated['provider']}",
            $creditCheck,
            null,
            $validated
        );

        // TODO: Dispatch the queued API job once the provider integrations are implemented.
        // ProcessCreditCheck::dispatch($creditCheck);

        return back()->with('success', 'Credit check requested successfully.');
    }

    // =========================================================================
    // Update
    // =========================================================================

    /**
     * Update an existing credit check record with scoring and response data.
     *
     * Captures the previous status and score for audit purposes before applying
     * changes. Automatically sets `completed_at` when the status transitions to
     * `completed`, but preserves any existing timestamp on other status values.
     *
     * @param  Request      $request      Incoming HTTP request with updated credit check fields.
     * @param  CreditCheck  $creditCheck  The bound credit check model instance.
     * @return RedirectResponse           Redirect back with a success flash message.
     *
     * @bodyParam string  status        required  New status — one of: pending, completed, failed.
     * @bodyParam int     credit_score  nullable  Credit score integer between 0 and 1200.
     * @bodyParam array   response_data nullable  Raw provider response payload.
     * @bodyParam string  notes         nullable  Internal notes; preserves existing value if omitted.
     */
    public function update(Request $request, CreditCheck $creditCheck): RedirectResponse
    {
        $validated = $this->validateCreditCheckUpdate($request);

        $oldValues = $this->captureAuditSnapshot($creditCheck);

        $this->applyCreditCheckUpdate($creditCheck, $validated);

        ActivityLog::logActivity(
            'updated',
            "Updated credit check status to {$validated['status']}",
            $creditCheck,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Credit check updated successfully.');
    }

    // =========================================================================
    // Display
    // =========================================================================

    /**
     * Display the credit check results and raw provider response data.
     *
     * Eager-loads the parent application and the admin user who requested
     * the check, so the view has full context without additional queries.
     *
     * @param  CreditCheck  $creditCheck  The bound credit check model instance.
     * @return View                       The `admin.credit-checks.show` view.
     */
    public function show(CreditCheck $creditCheck): View
    {
        $creditCheck->load(['application', 'requestedBy']);

        return view('admin.credit-checks.show', compact('creditCheck'));
    }

    // =========================================================================
    // Private Helpers — Validation
    // =========================================================================

    /**
     * Validate the incoming credit check initiation request.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `provider` and optional `notes` fields.
     */
    private function validateCreditCheckRequest(Request $request): array
    {
        return $request->validate([
            'provider' => ['required', 'string', 'in:' . implode(',', self::PROVIDERS)],
            'notes'    => ['nullable', 'string'],
        ]);
    }

    /**
     * Validate the incoming credit check update request.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated status, credit score, response data, and notes.
     */
    private function validateCreditCheckUpdate(Request $request): array
    {
        return $request->validate([
            'status'        => ['required', 'in:pending,completed,failed'],
            'credit_score'  => ['nullable', 'integer', 'min:0', 'max:1200'],
            'response_data' => ['nullable', 'array'],
            'notes'         => ['nullable', 'string'],
        ]);
    }

    // =========================================================================
    // Private Helpers — Persistence
    // =========================================================================

    /**
     * Create a new CreditCheck record in pending status for the given application.
     *
     * @param  Application  $application  The parent application.
     * @param  array        $validated    Validated provider and notes fields.
     * @return CreditCheck               The newly created credit check record.
     */
    private function createPendingCreditCheck(Application $application, array $validated): CreditCheck
    {
        return $application->creditChecks()->create([
            'provider'     => $validated['provider'],
            'notes'        => $validated['notes'] ?? null,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'status'       => 'pending',
        ]);
    }

    /**
     * Capture a before-snapshot of auditable fields for the activity log.
     *
     * @param  CreditCheck  $creditCheck  The credit check record before update.
     * @return array                      Associative array of previous field values.
     */
    private function captureAuditSnapshot(CreditCheck $creditCheck): array
    {
        return $creditCheck->only(['status', 'credit_score']);
    }

    /**
     * Apply validated update fields to the credit check record.
     *
     * Sets `completed_at` to the current timestamp when transitioning to the
     * `completed` status, and preserves the existing timestamp otherwise.
     * The `notes` field falls back to the current value when not supplied.
     *
     * @param  CreditCheck  $creditCheck  The credit check record to update.
     * @param  array        $validated    Validated update payload.
     * @return void
     */
    private function applyCreditCheckUpdate(CreditCheck $creditCheck, array $validated): void
    {
        $creditCheck->update([
            'status'        => $validated['status'],
            'credit_score'  => $validated['credit_score'] ?? null,
            'response_data' => $validated['response_data'] ?? null,
            'notes'         => $validated['notes'] ?? $creditCheck->notes,
            'completed_at'  => $validated['status'] === 'completed'
                                ? now()
                                : $creditCheck->completed_at,
        ]);
    }
}