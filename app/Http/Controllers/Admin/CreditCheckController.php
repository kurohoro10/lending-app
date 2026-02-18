<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CreditCheck;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Class CreditCheckController
 *
 * Manages the lifecycle of credit reporting and scoring requests.
 * Orchestrates external provider requests (Equifax, Experian, Credit Sense)
 * and records the resulting financial health data for the application.
 *
 * @package App\Http\Controllers\Admin
 */
class CreditCheckController extends Controller
{
    /**
     * Initiate a new credit check request for a specific application.
     *
     * @param Request     $request
     * @param Application $application
     * @return RedirectResponse
     */
    public function request(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:credit_sense,equifax,experian',
            'notes'    => 'nullable|string',
        ]);

        $creditCheck = $application->creditChecks()->create([
            'provider'     => $validated['provider'],
            'notes'        => $validated['notes'] ?? null,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'status'       => 'pending',
        ]);

        ActivityLog::logActivity(
            'requested',
            "Requested credit check from {$validated['provider']}",
            $creditCheck,
            null,
            $validated
        );

        // TODO: Queue credit check API job
        // ProcessCreditCheck::dispatch($creditCheck);

        return back()->with('success', 'Credit check requested successfully.');
    }

    /**
     * Update an existing credit check record with scoring and response data.
     *
     * @param Request     $request
     * @param CreditCheck $creditCheck
     * @return RedirectResponse
     */
    public function update(Request $request, CreditCheck $creditCheck): RedirectResponse
    {
        $validated = $request->validate([
            'status'        => 'required|in:pending,completed,failed',
            'credit_score'  => 'nullable|integer|min:0|max:1200',
            'response_data' => 'nullable|array',
            'notes'         => 'nullable|string',
        ]);

        $oldValues = $creditCheck->only(['status', 'credit_score']);

        $creditCheck->update([
            'status'        => $validated['status'],
            'credit_score'  => $validated['credit_score'] ?? null,
            'response_data' => $validated['response_data'] ?? null,
            'notes'         => $validated['notes'] ?? $creditCheck->notes,
            'completed_at'  => $validated['status'] === 'completed' ? now() : $creditCheck->completed_at,
        ]);

        ActivityLog::logActivity(
            'updated',
            "Updated credit check status to {$validated['status']}",
            $creditCheck,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Credit check updated successfully.');
    }

    /**
     * Display the specific credit check results and raw provider data.
     *
     * @param CreditCheck $creditCheck
     * @return View
     */
    public function show(CreditCheck $creditCheck): View
    {
        $creditCheck->load(['application', 'requestedBy']);

        return view('admin.credit-checks.show', compact('creditCheck'));
    }
}
