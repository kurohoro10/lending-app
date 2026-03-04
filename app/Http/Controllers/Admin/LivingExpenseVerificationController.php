<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LivingExpenseVerificationController extends Controller
{
    /**
     * Return data needed to populate the expense calculator modal.
     * Combines: client-stated living expenses + Basiq bank_api_report categories.
     */
    public function data(Application $application): JsonResponse
    {
        $application->load('livingExpenses');

        // ── Basiq report ─────────────────────────────────────────────────
        $basiqReport      = $application->bank_api_report ?? null;   // already cast to array on model
        $basiqConfigured  = filled(config('services.basiq.key'))
                         || \App\Models\Setting::where('key', 'basiq_api_key')->value('value');

        // Normalise Basiq spending categories into key => monthly_amount map.
        // Basiq Affordability report shape (v3):
        //   data.expenses.monthly.[ { category, value } ]
        // Adjust the path below if your webhook stores a different envelope.
        $basiqCategories = [];

        if (is_array($basiqReport)) {
            $rows = data_get($basiqReport, 'data.expenses.monthly', []);
            // Fallback: some Basiq report shapes use 'categories' at root
            if (empty($rows)) {
                $rows = data_get($basiqReport, 'categories', []);
            }
            foreach ($rows as $row) {
                $name  = $row['category'] ?? $row['name'] ?? null;
                $value = $row['value']    ?? $row['monthly_amount'] ?? 0;
                if ($name) {
                    $key = strtolower(str_replace([' ', '-'], '_', $name));
                    $basiqCategories[$key] = [
                        'label'         => $name,
                        'monthly_amount' => (float) $value,
                    ];
                }
            }
        }

        // ── Client-stated expenses ────────────────────────────────────────
        $clientExpenses = $application->livingExpenses->map(function ($e) use ($basiqCategories) {
            $normalised  = strtolower(str_replace([' ', '-'], '_', $e->expense_type ?? $e->expense_name ?? ''));
            $basiqMatch  = $basiqCategories[$normalised] ?? null;

            return [
                'id'           => $e->id,
                'description'  => $e->expense_type ?? $e->expense_name,
                'amount'       => (float) ($e->amount ?? $e->client_declared_amount ?? 0),
                'frequency'    => $e->frequency ?? 'monthly',
                'basiq_amount' => $basiqMatch ? $basiqMatch['monthly_amount'] : null,
                'basiq_label'  => $basiqMatch ? $basiqMatch['label']          : null,
            ];
        });

        // ── Previously saved verified expenses ────────────────────────────
        $verifiedExpenses = $application->verified_expenses
            ? (is_array($application->verified_expenses)
                ? $application->verified_expenses
                : json_decode($application->verified_expenses, true))
            : null;

        return response()->json([
            'basiq_configured'        => (bool) $basiqConfigured,
            'basiq_report_available'  => !empty($basiqReport),
            'report_received_at'      => $application->bank_api_report_received_at?->format('d M Y H:i'),
            'basiq_categories'        => array_values($basiqCategories),
            'client_expenses'         => $clientExpenses,
            'verified_expenses'       => $verifiedExpenses,
        ]);
    }

    /**
     * Save the admin's verified expense entries.
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'expenses'                   => 'required|array|min:1',
            'expenses.*.description'     => 'required|string|max:255',
            'expenses.*.amount'          => 'required|numeric|min:0',
            'expenses.*.frequency'       => 'required|in:weekly,fortnightly,monthly,quarterly,annually',
            'expenses.*.verified_amount' => 'required|numeric|min:0',
            'expenses.*.basiq_amount'    => 'nullable|numeric|min:0',
        ]);

        $application->update([
            'verified_expenses' => $validated['expenses'],
        ]);

        ActivityLog::logActivity(
            'expenses_verified',
            'Verified living expenses using Basiq bank data',
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
}
