<?php

namespace App\Services;

use App\Models\Application;

class AutoDeclineService
{
    /**
     * Check if application should be auto-declined
     * Returns array: ['should_decline' => bool, 'reason' => string|null]
     */
    public static function checkDeclineCriteria(Application $application): array
    {
        // Check 1: Trading Age - Must be 1 year+
        if (!self::hasSufficientTradingHistory($application)) {
            return [
                'should_decline' => true,
                'reason' => 'Insufficient Trading History - Business must be trading for at least 1 year'
            ];
        }

        // Check 2: Annual Turnover - Must be > $100k
        if (!self::meetsTurnoverRequirement($application)) {
            return [
                'should_decline' => true,
                'reason' => 'Revenue Too Low - Annual turnover must exceed $100,000'
            ];
        }

        // Check 3: Loan Amount Limits
        if (!self::meetsLoanAmountLimits($application)) {
            return [
                'should_decline' => true,
                'reason' => 'Loan amount outside acceptable range ($10,000 - $5,000,000)'
            ];
        }

        // All checks passed
        return [
            'should_decline' => false,
            'reason' => null
        ];
    }

    /**
     * Check if business has been trading for at least 1 year
     */
    private static function hasSufficientTradingHistory(Application $application): bool
    {
        $employment = $application->employmentDetails()
            ->where('employment_type', 'self_employed')
            ->orWhere('employment_type', 'company_director')
            ->first();

        if (!$employment || !$employment->employment_start_date) {
            return false;
        }

        $monthsTrading = now()->diffInMonths($employment->employment_start_date);
        return $monthsTrading >= 12;
    }

    /**
     * Check if annual turnover meets minimum $100k requirement
     */
    private static function meetsTurnoverRequirement(Application $application): bool
    {
        $totalAnnualIncome = $application->employmentDetails()
            ->get()
            ->sum(fn($emp) => $emp->getAnnualIncome());

        return $totalAnnualIncome >= 100000;
    }

    /**
     * Check if loan amount is within acceptable limits
     */
    private static function meetsLoanAmountLimits(Application $application): bool
    {
        $amount = $application->loan_amount;
        return $amount >= 10000 && $amount <= 5000000;
    }
}
