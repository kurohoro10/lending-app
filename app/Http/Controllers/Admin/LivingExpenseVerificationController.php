<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LivingExpenseVerificationController extends Controller
{
    /**
     * Save the admin's verified expense entries for an application.
     * The verified_expenses JSON column stores the admin's final assessed amounts.
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'expenses'                  => 'required|array|min:1',
            'expenses.*.description'    => 'required|string|max:255',
            'expenses.*.amount'         => 'required|numeric|min:0',
            'expenses.*.frequency'      => 'required|in:weekly,fortnightly,monthly,quarterly,annually',
            'expenses.*.verified_amount'=> 'required|numeric|min:0',
        ]);

        $application->update([
            'verified_expenses' => $validated['expenses'],
        ]);

        ActivityLog::logActivity(
            'expenses_verified',
            'Verified living expenses via expense calculator',
            $application,
            null,
            ['expense_count' => count($validated['expenses'])]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expenses verified and saved.',
            ]);
        }

        return back()->with('success', 'Expenses verified successfully.');
    }

    /**
     * Return the data needed to populate the expense calculator modal.
     * Combines: client-stated expenses, CreditSense report, and any
     * previously saved verified amounts.
     */
    public function data(Application $application): JsonResponse
    {
        $application->load('livingExpenses');

        $creditSenseConfigured = filled(Setting::get('creditsense_api_key'));
        $creditSenseReport     = $application->credit_sense_report
            ? json_decode($application->credit_sense_report, true)
            : null;

        // Normalize CreditSense categories into a flat key=>amount map
        // Expected report structure (adjust once you have real API docs):
        // { "categories": [ { "name": "Groceries", "monthly_amount": 450.00 }, ... ] }
        $bankStatementAmounts = [];
        if ($creditSenseReport && isset($creditSenseReport['categories'])) {
            foreach ($creditSenseReport['categories'] as $cat) {
                $key = strtolower(str_replace(' ', '_', $cat['name'] ?? ''));
                $bankStatementAmounts[$key] = $cat['monthly_amount'] ?? 0;
            }
        }

        // Client-stated expenses from living_expenses table
        $clientExpenses = $application->livingExpenses->map(fn($e) => [
            'id'          => $e->id,
            'description' => $e->expense_type,
            'amount'      => $e->amount,
            'frequency'   => $e->frequency ?? 'monthly',
            // Attempt to match a CreditSense category by normalized name
            'bank_amount' => $bankStatementAmounts[
                strtolower(str_replace([' ', '-'], '_', $e->expense_type))
            ] ?? null,
        ]);

        // Previously saved verified expenses (if admin already ran this)
        $verifiedExpenses = $application->verified_expenses
            ? json_decode($application->verified_expenses, true)
            : null;

        return response()->json([
            'creditsense_configured'   => $creditSenseConfigured,
            'creditsense_report'        => $creditSenseReport !== null,
            'report_received_at'        => $application->credit_sense_report_received_at?->format('d M Y H:i'),
            'client_expenses'           => $clientExpenses,
            'bank_statement_amounts'    => $bankStatementAmounts,
            'verified_expenses'         => $verifiedExpenses,
        ]);
    }
}
