<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\LivingExpense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LivingExpenseController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'expense_category' => 'required|string|max:100',
            'expense_name' => 'required|string|max:255',
            'client_declared_amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:weekly,fortnightly,monthly,quarterly,annual',
            'client_notes' => 'nullable|string',
        ]);

        $expense = $application->livingExpenses()->create($validated);

        ActivityLog::logActivity(
            'created',
            "Added living expense: {$validated['expense_name']}",
            $expense,
            null,
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Living expense added successfully.',
                'expense' => $expense,
                'type' => 'expense',
                'trigger_progress_update' => true
            ], 201);
        }

        return back()->with('success', 'Living expense added successfully.');
    }

    public function update(Request $request, Application $application, LivingExpense $livingExpense)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'expense_category' => 'required|string|max:100',
            'expense_name' => 'required|string|max:255',
            'client_declared_amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:weekly,fortnightly,monthly,quarterly,annual',
            'client_notes' => 'nullable|string',
        ]);

        $oldValues = $livingExpense->toArray();
        $livingExpense->update($validated);

        ActivityLog::logActivity(
            'updated',
            "Updated living expense: {$validated['expense_name']}",
            $livingExpense,
            $oldValues,
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Living expense updated successfully.',
                'expense' => $livingExpense,
                'type' => 'expense',
                'trigger_progress_update' => true
            ], 200);
        }

        return back()->with('success', 'Living expense updated successfully.');
    }

    public function destroy(Request $request, Application $application, LivingExpense $livingExpense)
    {
        $this->authorize('update', $application);

        $expenseName = $livingExpense->expense_name;
        $expenseId = $livingExpense->id;
        $livingExpense->delete();

        ActivityLog::logActivity(
            'deleted',
            "Deleted living expense: {$expenseName}",
            $application
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Living expense deleted successfully.',
                'deleted_id' => $expenseId,
                'type' => 'expense',
                'trigger_progress_update' => true
            ], 200);
        }

        return back()->with('success', 'Living expense deleted successfully.');
    }

    public function verify(Request $request, LivingExpense $livingExpense)
    {
        $this->authorize('verifyExpenses', $livingExpense->application);

        $validated = $request->validate([
            'verified_amount' => 'required|numeric|min:0',
            'assessor_notes' => 'nullable|string',
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

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Living expense verified successfully.',
                'expense' => $livingExpense
            ], 200);
        }

        return back()->with('success', 'Living expense verified successfully.');
    }
}
