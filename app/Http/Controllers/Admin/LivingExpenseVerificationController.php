<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\CreditSenseReportParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LivingExpenseVerificationController extends Controller
{
    /**
     * Return data needed to populate the expense calculator modal.
     *
     * Combines client-stated living expenses with bank data from the active
     * provider (CreditSense or Basiq). The response shape is provider-neutral —
     * expense-calculator.js reads the same structure regardless of provider.
     */
    public function data(Application $application): JsonResponse
    {
        $application->load('livingExpenses');

        $activeProvider = Setting::where('key', 'active_bank_provider')->value('value') ?? 'basiq';

        // ── Resolve bank categories from the active provider ──────────────
        [$bankCategories, $reportAvailable, $reportReceivedAt] = match ($activeProvider) {
            'creditsense' => $this->resolveCreditSenseCategories($application),
            default       => $this->resolveBasiqCategories($application),
        };

        // Build a normalised lookup: normalised_label => category data
        $bankLookup = [];
        foreach ($bankCategories as $cat) {
            $bankLookup[$this->normaliseLabel($cat['label'])] = $cat;
        }

        // ── Merge client expenses with bank matches ────────────────────────
        $clientExpenses = $application->livingExpenses->map(function ($expense) use ($bankLookup) {
            $name           = $expense->expense_name ?? '';
            $normalisedName = $this->normaliseLabel($name);

            // Exact match first, then partial
            $bankMatch = $bankLookup[$normalisedName]
                ?? $this->partialMatch($normalisedName, $bankLookup);

            return [
                'id'           => $expense->id,
                'description'  => $name,
                'amount'       => (float) ($expense->client_declared_amount ?? 0),
                'frequency'    => $expense->frequency ?? 'monthly',
                'basiq_amount' => $bankMatch ? round($bankMatch['monthly_amount'], 2) : null,
                'basiq_label'  => $bankMatch ? $bankMatch['label'] : null,
            ];
        });

        // ── Previously saved verified amounts ─────────────────────────────
        $verifiedExpenses = $application->livingExpenses
            ->filter(fn($e) => $e->verified_amount !== null)
            ->map(fn($e) => [
                'description'    => $e->expense_name ?? '',
                'verified_amount' => (float) $e->verified_amount,
            ])
            ->values()
            ->toArray();

        // ── All bank categories for the unmatched panel ───────────────────
        $allBankCategories = array_map(fn($cat) => [
            'label'          => $cat['label'],
            'monthly_amount' => round($cat['monthly_amount'], 2),
        ], $bankCategories);

        $providerLabel = match ($activeProvider) {
            'creditsense' => 'CreditSense',
            'basiq'       => 'Basiq',
            default       => Setting::where('key', 'bank_api_provider_name')->value('value') ?? 'Bank',
        };

        return response()->json([
            'basiq_configured'        => $reportAvailable,
            'basiq_report_available'  => $reportAvailable,
            'report_received_at'      => $reportReceivedAt,
            'basiq_categories'        => $allBankCategories,
            'client_expenses'         => $clientExpenses,
            'verified_expenses'       => $verifiedExpenses,
            'provider'                => $providerLabel,
        ]);
    }

    /**
     * Save the admin's verified expense entries back to each LivingExpense row.
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'expenses'                   => 'required|array|min:1',
            'expenses.*.description'     => 'required|string|max:255',
            'expenses.*.amount'          => 'required|numeric|min:0',
            'expenses.*.frequency'       => 'required|in:weekly,fortnightly,monthly,quarterly,annual',
            'expenses.*.verified_amount' => 'required|numeric|min:0',
            'expenses.*.basiq_amount'    => 'nullable|numeric|min:0',
        ]);

        $application->load('livingExpenses');

        foreach ($validated['expenses'] as $row) {
            $expense = $application->livingExpenses
                ->first(fn($e) => ($e->expense_name ?? '') === $row['description']);

            if ($expense) {
                $expense->update([
                    'verified_amount' => $row['verified_amount'],
                    'is_verified'     => true,
                    'verified_by'     => auth()->id(),
                    'verified_at'     => now(),
                ]);
            }
        }

        ActivityLog::logActivity(
            'expenses_verified',
            'Verified living expenses using bank data',
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

    // ── CreditSense category resolver ─────────────────────────────────────────

    /**
     * Parse the stored CreditSense report and return normalised expense categories.
     *
     * The CreditSense report structure stores expenses inside:
     *   Applications.Application.Accounts.Account[].Overviews.Overview.{H1}.{H2}[].MonthlyAmount
     *
     * Where H1 = category group (e.g. "Expenses") and H2 = subcategory (e.g. "Rent/Mortgage").
     * MonthlyAmount is already normalised to monthly by CreditSense.
     *
     * @return array [categories[], reportAvailable, reportReceivedAt]
     */
    private function resolveCreditSenseCategories(Application $application): array
    {
        $rawReport  = $application->credit_sense_report;
        $receivedAt = $application->credit_sense_report_received_at;

        if (blank($rawReport)) {
            return [[], false, null];
        }

        $parser     = new CreditSenseReportParser($rawReport);
        $categories = $parser->getExpenseCategories();

        return [
            $categories,
            ! empty($categories),
            $receivedAt?->format('d M Y H:i'),
        ];
    }

    // ── Basiq category resolver ───────────────────────────────────────────────

    /**
     * Extract expense categories from a Basiq report.
     * Basiq Affordability v3 shape: data.expenses.monthly[].{ category, value }
     *
     * @return array [categories[], reportAvailable, reportReceivedAt]
     */
    private function resolveBasiqCategories(Application $application): array
    {
        $rawReport  = $application->bank_api_report;
        $receivedAt = $application->bank_api_report_received_at;

        if (blank($rawReport)) {
            return [[], false, null];
        }

        $report = is_array($rawReport)
            ? $rawReport
            : json_decode($rawReport, true);

        if (! is_array($report)) {
            return [[], false, null];
        }

        // Try common Basiq report paths
        $rows = data_get($report, 'data.expenses.monthly',
                data_get($report, 'categories',
                data_get($report, 'expenses', [])));

        $categories = [];
        foreach ($rows as $row) {
            $label  = $row['category'] ?? $row['name'] ?? '';
            $amount = (float) ($row['value'] ?? $row['monthly_amount'] ?? 0);
            if ($label && $amount > 0) {
                $categories[] = [
                    'label'          => $label,
                    'monthly_amount' => $amount,
                    'category'       => 'Expenses',
                    'subcategory'    => $label,
                ];
            }
        }

        return [
            $categories,
            ! empty($categories),
            $receivedAt?->format('d M Y H:i'),
        ];
    }

    // ── Label matching helpers ────────────────────────────────────────────────

    /**
     * Normalise a label for comparison.
     * "Rent/Mortgage" → "rent_mortgage"
     */
    private function normaliseLabel(string $label): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($label)));
    }

    /**
     * Find a partial match in the bank lookup when no exact key match exists.
     * e.g. client expense "Rent" matches bank category "Rent/Mortgage"
     */
    private function partialMatch(string $normalisedName, array $bankLookup): ?array
    {
        foreach ($bankLookup as $bankKey => $bankCat) {
            if (
                str_contains($bankKey, $normalisedName) ||
                str_contains($normalisedName, $bankKey)
            ) {
                return $bankCat;
            }
        }

        return null;
    }
}