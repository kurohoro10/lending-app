<?php

namespace App\Services;

/**
 * CreditSenseReportParser
 *
 * Parses the CreditSense v2 JSON report structure into normalised arrays
 * suitable for the expense calculator modal.
 *
 * CreditSense report structure (stored in applications.credit_sense_report):
 *
 *   The raw report arrives in one of two shapes depending on how it was fetched:
 *
 *   Shape A — direct webhook / unencoded report:
 *     { "Applications": { "Application": { "AppID": ..., "Accounts": { "Account": [...] } } } }
 *
 *   Shape B — via REST API /report/download with CS_Report_Formats=["json"]:
 *     { "Success": true, "Response": { "attachments": [ { "type": "Credit Sense Report",
 *       "contentType": "json", "content": "<base64-encoded Shape A>" } ] } }
 *
 *   This parser handles both shapes transparently.
 *
 * Expense data lives in each Account's Overviews:
 *   Account.Overviews.Overview.{heading}.{subheading}[].{
 *     H1            — top-level category  e.g. "Expenses", "Income"
 *     H2            — subcategory         e.g. "Rent/Mortgage", "Salary"
 *     MonthlyAmount — already normalised to monthly
 *     TotalAmount   — total over the report period
 *     Count         — number of transactions
 *     FrequencyDescription — e.g. "Exactly Monthly"
 *   }
 *
 * Individual transactions are in Account.Transactions.Transaction[].{
 *     Category, TranAmount, TranBaseType (credit|debit), TranDate, CleanDesc
 * }
 */
class CreditSenseReportParser
{
    private array $report;

    public function __construct(array|string $rawReport)
    {
        if (is_string($rawReport)) {
            $rawReport = json_decode($rawReport, true) ?? [];
        }

        $this->report = $this->normalise($rawReport);
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Returns true if the report has been parsed successfully and contains accounts.
     */
    public function isValid(): bool
    {
        return ! empty($this->getAccounts());
    }

    /**
     * Return all categorised expense overviews, normalised as a flat array of:
     *   [
     *     'label'          => 'Rent/Mortgage',
     *     'category'       => 'Expenses',        // H1
     *     'subcategory'    => 'Rent/Mortgage',   // H2
     *     'monthly_amount' => 1200.00,
     *     'total_amount'   => 3600.00,
     *     'count'          => 3,
     *     'frequency'      => 'Exactly Monthly',
     *   ]
     *
     * Only debit (expense) categories are returned. Pass $includeIncome = true
     * to also include income categories.
     */
    public function getCategories(bool $includeIncome = false): array
    {
        $categories = [];
        $seen       = [];

        foreach ($this->getAccounts() as $account) {
            $overviewGroups = data_get($account, 'Overviews.Overview', []);

            foreach ($overviewGroups as $headingGroup) {
                foreach ($headingGroup as $subheadingGroup) {
                    if (! is_array($subheadingGroup)) continue;

                    foreach ($subheadingGroup as $entry) {
                        if (! is_array($entry)) continue;

                        $h1           = $entry['H1'] ?? '';
                        $h2           = $entry['H2'] ?? '';
                        $monthlyAmt   = (float) ($entry['MonthlyAmount'] ?? 0);
                        $totalAmt     = (float) ($entry['TotalAmount']   ?? 0);
                        $count        = (int)   ($entry['Count']         ?? 0);
                        $frequency    = $entry['FrequencyDescription']   ?? '';

                        // Skip income unless explicitly requested
                        if (! $includeIncome && strtolower($h1) === 'income') {
                            continue;
                        }

                        // Skip zero-amount categories
                        if ($monthlyAmt <= 0) continue;

                        // Deduplicate by H2 label — sum amounts if the same
                        // category appears across multiple accounts
                        $key = strtolower(trim($h2));
                        if (isset($seen[$key])) {
                            $categories[$seen[$key]]['monthly_amount'] += $monthlyAmt;
                            $categories[$seen[$key]]['total_amount']   += $totalAmt;
                            $categories[$seen[$key]]['count']          += $count;
                        } else {
                            $seen[$key] = count($categories);
                            $categories[] = [
                                'label'          => $h2,
                                'category'       => $h1,
                                'subcategory'    => $h2,
                                'monthly_amount' => $monthlyAmt,
                                'total_amount'   => $totalAmt,
                                'count'          => $count,
                                'frequency'      => $frequency,
                            ];
                        }
                    }
                }
            }
        }

        // Sort by monthly amount descending for easier scanning
        usort($categories, fn($a, $b) => $b['monthly_amount'] <=> $a['monthly_amount']);

        return $categories;
    }

    /**
     * Return expense categories only (excludes Income).
     * This is the method the expense calculator uses.
     */
    public function getExpenseCategories(): array
    {
        return $this->getCategories(includeIncome: false);
    }

    /**
     * Return income categories only.
     */
    public function getIncomeCategories(): array
    {
        $all    = $this->getCategories(includeIncome: true);
        return array_values(
            array_filter($all, fn($c) => strtolower($c['category']) === 'income')
        );
    }

    /**
     * Return a flat array of all debit transactions across all accounts:
     *   [ 'date', 'description', 'category', 'amount', 'account_name' ]
     */
    public function getTransactions(bool $debitsOnly = true): array
    {
        $transactions = [];

        foreach ($this->getAccounts() as $account) {
            $accountName = $account['AccountName'] ?? $account['BankName'] ?? 'Unknown Account';
            $txns        = data_get($account, 'Transactions.Transaction', []);

            // Normalise single-transaction responses (object instead of array)
            if (isset($txns['TranID'])) {
                $txns = [$txns];
            }

            foreach ($txns as $txn) {
                $baseType = strtolower($txn['TranBaseType'] ?? '');
                if ($debitsOnly && $baseType !== 'debit') continue;

                $transactions[] = [
                    'date'         => $txn['TranDate']    ?? null,
                    'description'  => $txn['CleanDesc']   ?? '',
                    'category'     => $txn['Category']    ?? '',
                    'amount'       => abs((float) ($txn['TranAmount'] ?? 0)),
                    'account_name' => $accountName,
                ];
            }
        }

        // Sort by date descending
        usort($transactions, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return $transactions;
    }

    /**
     * Return high-level account summary information.
     */
    public function getAccountSummaries(): array
    {
        $summaries = [];

        foreach ($this->getAccounts() as $account) {
            $summaries[] = [
                'account_id'      => $account['AccountID']      ?? null,
                'account_name'    => $account['AccountName']    ?? '',
                'account_type'    => $account['AccountType']    ?? '',
                'account_holder'  => $account['AccountHolder']  ?? '',
                'bank_name'       => $account['BankName']       ?? '',
                'current_balance' => (float) ($account['BankCurrentBalance'] ?? 0),
                'total_credits'   => (float) ($account['TotalCredits']       ?? 0),
                'total_debits'    => (float) ($account['TotalDebits']        ?? 0),
                'dishonour_count' => (int)   ($account['DishonourCount']     ?? 0),
                'days_overdraft'  => (int)   ($account['DaysOverdraft']      ?? 0),
                'days_range'      => (int)   ($account['DaysRange']          ?? 90),
            ];
        }

        return $summaries;
    }

    /**
     * Return the application-level metadata from the report.
     */
    public function getMeta(): array
    {
        $app = $this->getApplication();

        return [
            'app_id'        => $app['AppID']        ?? null,
            'app_reference' => $app['AppReference'] ?? null,
            'report_id'     => $app['ReportID']     ?? null,
            'created_at'    => $app['CreateDT']     ?? null,
            'days_range'    => $app['daysRange']    ?? 90,
            'store_code'    => $app['StoreCode']    ?? null,
            'store_name'    => $app['StoreName']    ?? null,
        ];
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    /**
     * Normalise both Shape A and Shape B into a consistent internal structure.
     * Always resolves to the decoded Applications.Application object.
     */
    private function normalise(array $raw): array
    {
        // Shape B — REST API response with base64-encoded attachment
        if (isset($raw['Success'], $raw['Response']['attachments'])) {
            foreach ($raw['Response']['attachments'] as $attachment) {
                if (
                    ($attachment['type'] ?? '') === 'Credit Sense Report' &&
                    ($attachment['contentType'] ?? '') === 'json' &&
                    ! empty($attachment['content'])
                ) {
                    $decoded = base64_decode($attachment['content'], strict: true);
                    if ($decoded !== false) {
                        $parsed = json_decode($decoded, true);
                        if (is_array($parsed)) {
                            return $this->normalise($parsed);
                        }
                    }
                }
            }
        }

        // Shape A — direct Applications wrapper
        if (isset($raw['Applications']['Application'])) {
            return $raw;
        }

        // Already normalised or unknown shape — return as-is
        return $raw;
    }

    private function getApplication(): array
    {
        return $this->report['Applications']['Application'] ?? [];
    }

    private function getAccounts(): array
    {
        $accounts = data_get($this->report, 'Applications.Application.Accounts.Account', []);

        // Normalise single-account responses (object instead of array)
        if (isset($accounts['AccountID'])) {
            return [$accounts];
        }

        return is_array($accounts) ? $accounts : [];
    }
}