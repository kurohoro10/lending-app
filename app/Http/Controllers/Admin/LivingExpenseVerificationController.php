<?php

/**
 * @file    app/Http/Controllers/Admin/LivingExpenseVerificationController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages admin verification of client-declared living expenses against bank
 * data within the commercial loan application system.
 *
 * Responsibilities:
 *  - Resolving bank expense categories from the active provider (CreditSense or Basiq)
 *  - Merging client-stated expenses with matched bank categories for the calculator modal
 *  - Persisting admin-verified expense amounts back to each LivingExpense row
 *  - Providing a provider-neutral response shape consumed by expense-calculator.js
 *
 * Supported bank providers (configured via settings key `active_bank_provider`):
 *  - `creditsense` — parsed via CreditSenseReportParser; source: `credit_sense_report`
 *  - `basiq`       — parsed inline; source: `bank_api_report` (Affordability v3 shape)
 *
 * Progressive enhancement (store):
 *  - JS present (`Accept: application/json`) → JsonResponse
 *  - JS absent  (plain form POST)            → RedirectResponse with flash message
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Setting;
use App\Services\CreditSenseReportParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LivingExpenseVerificationController extends Controller
{
    /**
     * Provider identifier for CreditSense.
     *
     * @var string
     */
    private const PROVIDER_CREDITSENSE = 'creditsense';

    /**
     * Provider identifier for Basiq.
     *
     * @var string
     */
    private const PROVIDER_BASIQ = 'basiq';

    /**
     * Human-readable display labels for each known provider identifier.
     *
     * Falls back to the `bank_api_provider_name` setting for unknown providers.
     *
     * @var array<string, string>
     */
    private const PROVIDER_LABELS = [
        self::PROVIDER_CREDITSENSE => 'CreditSense',
        self::PROVIDER_BASIQ       => 'Basiq',
    ];

    // =========================================================================
    // Data (Modal Payload)
    // =========================================================================

    /**
     * Return the data payload needed to populate the expense calculator modal.
     *
     * Combines client-stated living expenses with bank-sourced category data
     * from the active provider. The response shape is provider-neutral so that
     * expense-calculator.js reads the same structure regardless of provider.
     *
     * Response keys:
     *  - `basiq_configured`       — whether a report is available (legacy key, kept for JS compat)
     *  - `basiq_report_available` — alias of the above
     *  - `report_received_at`     — formatted timestamp of the report, or null
     *  - `basiq_categories`       — all bank categories for the unmatched panel
     *  - `client_expenses`        — client expenses merged with bank matches
     *  - `verified_expenses`      — previously saved verified amounts
     *  - `provider`               — display label for the active provider
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Provider-neutral expense modal payload.
     */
    public function data(Application $application): JsonResponse
    {
        $application->load('livingExpenses');

        $activeProvider = $this->resolveActiveProvider();

        [$bankCategories, $reportAvailable, $reportReceivedAt] = $this->resolveBankCategories(
            $application,
            $activeProvider
        );

        $bankLookup = $this->buildBankLookup($bankCategories);

        $clientExpenses   = $this->buildClientExpenses($application->livingExpenses, $bankLookup);
        $verifiedExpenses = $this->buildVerifiedExpenses($application->livingExpenses);
        $allBankCategories = $this->formatBankCategories($bankCategories);
        $providerLabel    = $this->resolveProviderLabel($activeProvider);

        return response()->json([
            'basiq_configured'       => $reportAvailable,
            'basiq_report_available' => $reportAvailable,
            'report_received_at'     => $reportReceivedAt,
            'basiq_categories'       => $allBankCategories,
            'client_expenses'        => $clientExpenses,
            'verified_expenses'      => $verifiedExpenses,
            'provider'               => $providerLabel,
        ]);
    }

    // =========================================================================
    // Store (Verified Expenses)
    // =========================================================================

    /**
     * Save the admin's verified expense amounts back to each LivingExpense row.
     *
     * Iterates the submitted expense list and matches each row to the
     * corresponding LivingExpense record by `expense_name`. Matched records
     * are marked verified with the current admin's identity and timestamp.
     * Unmatched rows are silently skipped.
     *
     * @param  Request      $request      Incoming HTTP request with verified expense list.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse|RedirectResponse
     *
     * @bodyParam array   expenses                   required  List of verified expense entries.
     * @bodyParam string  expenses.*.description     required  Expense name (max 255 chars).
     * @bodyParam numeric expenses.*.amount          required  Client-declared amount (min 0).
     * @bodyParam string  expenses.*.frequency       required  One of: weekly, fortnightly, monthly, quarterly, annual.
     * @bodyParam numeric expenses.*.verified_amount required  Admin-verified amount (min 0).
     * @bodyParam numeric expenses.*.basiq_amount    nullable  Bank-sourced amount for reference (min 0).
     *
     * @response 200 { "success": true, "message": "Expenses verified and saved." }
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $this->validateExpensePayload($request);

        $application->load('livingExpenses');

        $this->persistVerifiedExpenses($application, $validated['expenses']);

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

    // =========================================================================
    // Private Helpers — Provider Resolution
    // =========================================================================

    /**
     * Resolve the active bank provider identifier from settings.
     *
     * Defaults to `basiq` if the setting is absent or blank.
     *
     * @return string  The provider identifier string.
     */
    private function resolveActiveProvider(): string
    {
        return Setting::where('key', 'active_bank_provider')->value('value') ?? self::PROVIDER_BASIQ;
    }

    /**
     * Dispatch to the appropriate category resolver for the active provider.
     *
     * @param  Application  $application    The application whose report is read.
     * @param  string       $activeProvider The active provider identifier.
     * @return array                        Tuple: [categories[], reportAvailable, reportReceivedAt].
     */
    private function resolveBankCategories(Application $application, string $activeProvider): array
    {
        return match ($activeProvider) {
            self::PROVIDER_CREDITSENSE => $this->resolveCreditSenseCategories($application),
            default                    => $this->resolveBasiqCategories($application),
        };
    }

    /**
     * Resolve the human-readable provider label for the JSON response.
     *
     * Falls back to the `bank_api_provider_name` setting for unknown providers,
     * and further falls back to the generic string `'Bank'` if that is also unset.
     *
     * @param  string  $activeProvider  The active provider identifier.
     * @return string                   Display label for the provider.
     */
    private function resolveProviderLabel(string $activeProvider): string
    {
        return self::PROVIDER_LABELS[$activeProvider]
            ?? Setting::where('key', 'bank_api_provider_name')->value('value')
            ?? 'Bank';
    }

    // =========================================================================
    // Private Helpers — CreditSense
    // =========================================================================

    /**
     * Parse the stored CreditSense report and return normalised expense categories.
     *
     * The CreditSense report stores expenses inside:
     *   Applications.Application.Accounts.Account[].Overviews.Overview.{H1}.{H2}[].MonthlyAmount
     *
     * Where H1 = category group (e.g. "Expenses") and H2 = subcategory (e.g. "Rent/Mortgage").
     * MonthlyAmount is already normalised to a monthly figure by CreditSense.
     *
     * @param  Application  $application  The application whose CreditSense report is parsed.
     * @return array                      Tuple: [categories[], reportAvailable, reportReceivedAt].
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

    // =========================================================================
    // Private Helpers — Basiq
    // =========================================================================

    /**
     * Extract expense categories from a stored Basiq Affordability v3 report.
     *
     * Attempts multiple common Basiq response paths in order:
     *  1. `data.expenses.monthly`
     *  2. `categories`
     *  3. `expenses`
     *
     * Each row may use either `category`/`value` or `name`/`monthly_amount` keys.
     * Rows with no label or a zero amount are silently skipped.
     *
     * @param  Application  $application  The application whose Basiq report is parsed.
     * @return array                      Tuple: [categories[], reportAvailable, reportReceivedAt].
     */
    private function resolveBasiqCategories(Application $application): array
    {
        $rawReport  = $application->bank_api_report;
        $receivedAt = $application->bank_api_report_received_at;

        if (blank($rawReport)) {
            return [[], false, null];
        }

        $report = is_array($rawReport) ? $rawReport : json_decode($rawReport, true);

        if (! is_array($report)) {
            return [[], false, null];
        }

        $rows = data_get($report, 'data.expenses.monthly',
                data_get($report, 'categories',
                data_get($report, 'expenses', [])));

        $categories = $this->parseBasiqRows($rows);

        return [
            $categories,
            ! empty($categories),
            $receivedAt?->format('d M Y H:i'),
        ];
    }

    /**
     * Convert raw Basiq expense rows into the normalised category shape.
     *
     * @param  array  $rows  Raw rows from the Basiq report payload.
     * @return array         Normalised category array, excluding zero-amount entries.
     */
    private function parseBasiqRows(array $rows): array
    {
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

        return $categories;
    }

    // =========================================================================
    // Private Helpers — Data Merge
    // =========================================================================

    /**
     * Build a normalised label → category lookup from the bank categories array.
     *
     * @param  array  $bankCategories  The resolved bank category list.
     * @return array                   Associative array keyed by normalised label.
     */
    private function buildBankLookup(array $bankCategories): array
    {
        $lookup = [];

        foreach ($bankCategories as $cat) {
            $lookup[$this->normaliseLabel($cat['label'])] = $cat;
        }

        return $lookup;
    }

    /**
     * Merge client living expenses with their best-matching bank category.
     *
     * Attempts an exact normalised-label match first, then falls back to a
     * partial substring match (e.g. client "Rent" matches bank "Rent/Mortgage").
     *
     * @param  Collection  $livingExpenses  The application's LivingExpense models.
     * @param  array       $bankLookup      Normalised label → bank category map.
     * @return Collection                   Formatted client expense entries.
     */
    private function buildClientExpenses(Collection $livingExpenses, array $bankLookup): Collection
    {
        return $livingExpenses->map(function ($expense) use ($bankLookup) {
            $name           = $expense->expense_name ?? '';
            $normalisedName = $this->normaliseLabel($name);

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
    }

    /**
     * Build the previously verified expense list from the application's living expenses.
     *
     * Filters to rows where a `verified_amount` has already been saved.
     *
     * @param  Collection  $livingExpenses  The application's LivingExpense models.
     * @return array                        Array of verified expense entries.
     */
    private function buildVerifiedExpenses(Collection $livingExpenses): array
    {
        return $livingExpenses
            ->filter(fn ($e) => $e->verified_amount !== null)
            ->map(fn ($e) => [
                'description'     => $e->expense_name ?? '',
                'verified_amount' => (float) $e->verified_amount,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Format the raw bank categories for the unmatched-category panel in the modal.
     *
     * Rounds `monthly_amount` to 2 decimal places and strips provider-internal fields.
     *
     * @param  array  $bankCategories  The resolved bank category list.
     * @return array                   Formatted category entries for the JSON response.
     */
    private function formatBankCategories(array $bankCategories): array
    {
        return array_map(fn ($cat) => [
            'label'          => $cat['label'],
            'monthly_amount' => round($cat['monthly_amount'], 2),
        ], $bankCategories);
    }

    // =========================================================================
    // Private Helpers — Label Matching
    // =========================================================================

    /**
     * Normalise a label string for case- and punctuation-insensitive comparison.
     *
     * Example: `"Rent/Mortgage"` → `"rent_mortgage"`
     *
     * @param  string  $label  The raw label string to normalise.
     * @return string          Lowercase, underscore-separated identifier string.
     */
    private function normaliseLabel(string $label): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($label)));
    }

    /**
     * Find a partial substring match in the bank lookup when no exact key match exists.
     *
     * Checks both directions: bank key contains client key, and client key contains
     * bank key. This handles cases such as client "Rent" matching bank "Rent/Mortgage".
     *
     * @param  string  $normalisedName  The normalised client expense name.
     * @param  array   $bankLookup      Normalised label → bank category map.
     * @return array|null               The first matching bank category, or null.
     */
    private function partialMatch(string $normalisedName, array $bankLookup): ?array
    {
        foreach ($bankLookup as $bankKey => $bankCat) {
            if (str_contains($bankKey, $normalisedName) || str_contains($normalisedName, $bankKey)) {
                return $bankCat;
            }
        }

        return null;
    }

    // =========================================================================
    // Private Helpers — Validation & Persistence
    // =========================================================================

    /**
     * Validate the verified expense submission payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `expenses` array.
     */
    private function validateExpensePayload(Request $request): array
    {
        return $request->validate([
            'expenses'                   => ['required', 'array', 'min:1'],
            'expenses.*.description'     => ['required', 'string', 'max:255'],
            'expenses.*.amount'          => ['required', 'numeric', 'min:0'],
            'expenses.*.frequency'       => ['required', 'in:weekly,fortnightly,monthly,quarterly,annual'],
            'expenses.*.verified_amount' => ['required', 'numeric', 'min:0'],
            'expenses.*.basiq_amount'    => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Persist verified amounts for each matched LivingExpense row.
     *
     * Matches each submitted row to a LivingExpense by `expense_name`. Matched
     * records are updated with the verified amount, admin identity, and timestamp.
     * Rows with no matching LivingExpense are silently skipped.
     *
     * @param  Application  $application  The application whose expenses are updated.
     * @param  array        $expenses     The validated list of expense rows.
     * @return void
     */
    private function persistVerifiedExpenses(Application $application, array $expenses): void
    {
        foreach ($expenses as $row) {
            $expense = $application->livingExpenses
                ->first(fn ($e) => ($e->expense_name ?? '') === $row['description']);

            if (! $expense) {
                continue;
            }

            $expense->update([
                'verified_amount' => $row['verified_amount'],
                'is_verified'     => true,
                'verified_by'     => auth()->id(),
                'verified_at'     => now(),
            ]);
        }
    }
}