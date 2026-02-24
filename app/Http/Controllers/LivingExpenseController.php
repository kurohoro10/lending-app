<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\LivingExpense;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LivingExpenseController extends Controller
{
    private const FREQUENCIES = [
        'weekly'      => 52 / 12,
        'fortnightly' => 26 / 12,
        'monthly'     => 1,
        'quarterly'   => 1 / 3,
        'annual'      => 1 / 12,
    ];

    /**
     * Bulk upsert all expense rows submitted from the living expenses form.
     * Rows with amount = 0 or blank are skipped (not saved / removed if previously saved).
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'expenses'                              => 'required|array',
            'expenses.*.expense_category'           => 'required|string|max:100',
            'expenses.*.expense_name'               => 'nullable|string|max:255',
            'expenses.*.client_declared_amount'     => 'nullable|numeric|min:0',
            'expenses.*.frequency'                  => 'required|in:weekly,fortnightly,monthly,quarterly,annual',
            'expenses.*.client_notes'               => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Delete all existing expenses for this application — we replace wholesale
            $application->livingExpenses()->delete();

            $saved = [];
            foreach ($validated['expenses'] as $row) {
                // Skip "other" rows with no name — standard rows always save (even at $0)
                if (empty(trim($row['expense_name'] ?? ''))) continue;

                $expense = $application->livingExpenses()->create([
                    'expense_category'       => $row['expense_category'],
                    'expense_name'           => $row['expense_name'],
                    'client_declared_amount' => (float) ($row['client_declared_amount'] ?? 0),
                    'frequency'              => $row['frequency'],
                    'client_notes'           => $row['client_notes'] ?? null,
                ]);

                $saved[] = $expense;
            }

            ActivityLog::logActivity(
                'updated',
                'Living expenses updated (' . count($saved) . ' item(s) saved)',
                $application,
                null,
                ['count' => count($saved)]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to save living expenses: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save expenses. Please try again.',
                ], 500);
            }
            return back()->with('error', 'Failed to save expenses. Please try again.');
        }

        $totalMonthly = collect($saved)->sum(
            fn($e) => $e->client_declared_amount * (self::FREQUENCIES[$e->frequency] ?? 1)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success'                => true,
                'message'                => count($saved) . ' expense(s) saved successfully.',
                'expenses'               => $saved,
                'total_monthly'          => round($totalMonthly, 2),
                'trigger_progress_update' => true,
            ]);
        }

        return back()->with('success', count($saved) . ' expense(s) saved successfully.');
    }

    /**
     * Delete a single expense (used only if individual delete is ever needed).
     */
    public function destroy(Request $request, Application $application, LivingExpense $livingExpense): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $application);

        $expenseName = $livingExpense->expense_name;
        $expenseId   = $livingExpense->id;
        $livingExpense->delete();

        ActivityLog::logActivity('deleted', "Deleted living expense: {$expenseName}", $application);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Living expense deleted successfully.',
                'deleted_id' => $expenseId,
                'type'       => 'expense',
                'trigger_progress_update' => true,
            ]);
        }

        return back()->with('success', 'Living expense deleted successfully.');
    }

    /**
     * Admin verifies a single expense line.
     */
    public function verify(Request $request, LivingExpense $livingExpense): JsonResponse|RedirectResponse
    {
        $this->authorize('verifyExpenses', $livingExpense->application);

        $validated = $request->validate([
            'verified_amount'    => 'required|numeric|min:0',
            'assessor_notes'     => 'nullable|string',
            'verification_notes' => 'nullable|string',
        ]);

        $oldValues = $livingExpense->only(['verified_amount', 'is_verified']);

        $livingExpense->update(array_merge($validated, [
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]));

        ActivityLog::logActivity(
            'verified',
            "Verified living expense: {$livingExpense->expense_name}",
            $livingExpense,
            $oldValues,
            $validated
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Living expense verified successfully.',
                'expense' => $livingExpense,
            ]);
        }

        return back()->with('success', 'Living expense verified successfully.');
    }
}
