<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class CreditSensePdfParser
{
    private string $text;

    public function __construct(string $pdfPath)
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($pdfPath);
        $this->text = $pdf->getText();
    }

    /**
     * Extract expense categories with their ongoing monthly amounts.
     * Returns the same shape as CreditSenseReportParser::getExpenseCategories()
     */
    public function getExpenseCategories(): array
    {
        $expenseCategories = [
            'Housing and Utilities',
            'Internet Telephone and Pay TV',
            'Groceries',
            'Recreation and Entertainment',
            'Clothing and Personal Care',
            'Medical and Health',
            'Transport',
            'Education and Childcare',
            'Insurance',
            'ATM',
            'Dishonours-',
            'Transfers',
            'Loans- Small amount loans',
            'Loans- Small amount repayments',
            'Loans- Other loans',
            'Loans- Other repayments',
            'Other Expenses- Fees',
            'Other Expenses- Known Expenses',
            'Other Expenses',
        ];

        $categories              = [];
        $otherTotal              = 0.0;
        $loansTotal              = 0.0;
        $otherExpensesSearchFrom = 0;

        foreach ($expenseCategories as $categoryName) {
            $searchFrom = ($categoryName === 'Other Expenses') ? $otherExpensesSearchFrom : 0;

            $amount = $this->extractOngoingMonthly($categoryName, $searchFrom);

            if (str_starts_with($categoryName, 'Other Expenses-')) {
                $pos = strpos($this->text, $categoryName);
                if ($pos !== false) {
                    $otherExpensesSearchFrom = max($otherExpensesSearchFrom, $pos + strlen($categoryName));
                }
            }

            if ($amount === null || $amount <= 0) continue;

            if (str_starts_with($categoryName, 'Loans-')) {
                $loansTotal += $amount;
                continue;
            }

            if (str_starts_with($categoryName, 'Other Expenses')) {
                $otherTotal += $amount;
                continue;
            }

            // Normalise display label
            $label = match(true) {
                $categoryName === 'Dishonours-' => 'Dishonours',
                $categoryName === 'Transfers'   => 'Transfers',
                $categoryName === 'ATM'         => 'ATM',
                default                         => $categoryName,
            };

            $categories[] = [
                'label'          => $label,
                'category'       => 'Expenses',
                'subcategory'    => $label,
                'monthly_amount' => $amount,
                'total_amount'   => 0,
                'count'          => 0,
                'frequency'      => 'monthly',
            ];
        }

        if ($loansTotal > 0) {
            $categories[] = [
                'label'          => 'Loans',
                'category'       => 'Expenses',
                'subcategory'    => 'Loans',
                'monthly_amount' => $loansTotal,
                'total_amount'   => 0,
                'count'          => 0,
                'frequency'      => 'monthly',
            ];
        }

        if ($otherTotal > 0) {
            $categories[] = [
                'label'          => 'Other Expenses',
                'category'       => 'Expenses',
                'subcategory'    => 'Other Expenses',
                'monthly_amount' => $otherTotal,
                'total_amount'   => 0,
                'count'          => 0,
                'frequency'      => 'monthly',
            ];
        }

        usort($categories, fn($a, $b) => $b['monthly_amount'] <=> $a['monthly_amount']);

        return $categories;
    }

    private function extractOngoingMonthly(string $categoryName, int $searchFrom = 0): ?float
    {
        // ATM appears mid-text in income transactions as "(ATM CR)"
        // so we must find it as a standalone section heading only
        if ($categoryName === 'ATM') {
            return $this->extractAtmSection();
        }

        $pos = $searchFrom > 0
            ? strpos($this->text, $categoryName, $searchFrom)
            : strpos($this->text, $categoryName);

        if ($pos === false) {
            \Log::info("[CS PDF] ❌ NOT FOUND: {$categoryName}");
            return null;
        }

        $section = substr($this->text, $pos);

        // Limit to just past Total Non-Periodic Amount to avoid overrunning
        $totalPos = strpos($section, 'Total Non-Periodic Amount');
        if ($totalPos !== false) {
            $section = substr($section, 0, $totalPos + 300);
        }

        $escaped = str_replace(' ', '\s+', preg_quote($categoryName, '/'));
        $pattern = '/' . $escaped . '(.*?)(Ongoing Monthly Amount:\s*\$[^\n\r]*)/s';

        if (!preg_match($pattern, $section, $matches)) {
            \Log::info("[CS PDF] ❌ NOT FOUND: {$categoryName}");
            return null;
        }

        $block = $matches[1] . $matches[2];

        if (str_contains($block, 'No Matches')) {
            \Log::info("[CS PDF] 🚫 NO MATCHES: {$categoryName}");
            return null;
        }

        if (preg_match('/Ongoing Monthly Amount:\s*\$\s*(-?\d[\d,\.]*)/', $block, $numMatch)) {
            $raw   = str_replace(',', '', $numMatch[1]);
            $value = abs((float) $raw);
            \Log::info("[CS PDF] ✅ {$categoryName} = {$value}");
            return $value;
        }

        if (str_contains($block, 'Ongoing Monthly Amount')) {
            \Log::warning("[CS PDF] ⚠️ FOUND LINE BUT FAILED PARSE: {$categoryName}");
            \Log::warning("[CS PDF] LINE: " . trim($matches[2]));
        }

        \Log::warning("[CS PDF] ❌ NO VALUE: {$categoryName}");
        return null;
    }

    private function extractAtmSection(): ?float
    {
        // The ATM section heading appears as "ATM\n" followed by "Description"
        // NOT as "(ATM CR)" or "(ATM)" which appear mid-transaction
        // Search for ATM where the next non-whitespace word is "Description"
        $offset = 0;

        while (($pos = strpos($this->text, 'ATM', $offset)) !== false) {
            $offset = $pos + 1;

            // Check what comes before — should not be "(" which indicates mid-transaction
            $charBefore = $pos > 0 ? substr($this->text, $pos - 1, 1) : '';
            if ($charBefore === '(') continue;

            // Check ahead — should have "Description" within 150 chars
            $ahead = substr($this->text, $pos, 150);
            if (!str_contains($ahead, 'Description')) continue;

            // Found the section — now extract it with boundary
            $section  = substr($this->text, $pos);
            $totalPos = strpos($section, 'Total Non-Periodic Amount');
            if ($totalPos === false) continue;

            $summaryArea = substr($section, $totalPos, 300);

            if (preg_match('/Ongoing Monthly Amount:\s*\$\s*(-?\d[\d,\.]*)/', $summaryArea, $matches)) {
                $raw   = str_replace(',', '', $matches[1]);
                $value = abs((float) $raw);
                \Log::info("[CS PDF] ✅ ATM = {$value}");
                return $value;
            }

            // Found section but no ongoing amount (dash) — correctly return null
            \Log::info("[CS PDF] 🚫 ATM has no ongoing monthly amount");
            return null;
        }

        \Log::info("[CS PDF] ❌ NOT FOUND: ATM section");
        return null;
    }

    public function toReportArray(): array
    {
        return [
            'source'     => 'pdf_upload',
            'categories' => $this->getExpenseCategories(),
        ];
    }
}