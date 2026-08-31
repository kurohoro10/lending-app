<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\BorrowerDirector;
use App\Models\DirectorLivingExpense;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DirectorExpenseController extends Controller
{
    public function show(Application $application, BorrowerDirector $director): View
    {
        abort_if(
            $director->hasSubmittedExpenses(),
            403,
            'You have already submitted your expenses.'
        );

        return view('director.expenses', compact('application', 'director'));
    }

    public function store(Request $request, Application $application, BorrowerDirector $director): RedirectResponse
    {
        abort_if(
            $director->hasSubmittedExpenses(),
            403,
            'You have already submitted your expenses.'
        );

        $validated = $request->validate([
            'expenses'                          => 'required|array',
            'expenses.*.expense_category'       => 'required|string|max:100',
            'expenses.*.expense_name'           => 'required|string|max:255',
            'expenses.*.client_declared_amount' => 'nullable|numeric|min:0',
            'expenses.*.frequency'              => 'required|in:weekly,fortnightly,monthly,quarterly,annual',
            'expenses.*.client_notes'           => 'nullable|string|max:1000',
        ]);

        DirectorLivingExpense::create([
            'application_id'       => $application->id,
            'borrower_director_id' => $director->id,
            'expenses'             => $validated['expenses'],
            'submitted_at'         => now(),
            'submitted_ip'         => $request->ip(),
        ]);

        ActivityLog::logActivity(
            'director_expenses_submitted',
            'Director ' . $director->full_name . ' submitted living expenses',
            $application
        );

        return back()->with('success', 'Thank you. Your expenses have been submitted successfully.');
    }
}