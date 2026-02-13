<?php

namespace App\Services;

use App\Models\Application;
use App\Helpers\AgeCalculator;
use Carbon\Carbon;

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
     * File: app/Services/AutoDeclineService.php
     *
     * Determines whether the applicant has at least 12 months
     * of legally valid trading history.
     */
    private static function hasSufficientTradingHistory(Application $application): bool
    {
        $personal = $application->personalDetails;

        // ❌ Missing DOB = cannot verify legality
        if (!$personal || !$personal->date_of_birth) {
            return false;
        }

        $employment = $application->employmentDetails()
            ->whereIn('employment_type', [
                'self_employed',
                'company_director',
            ])
            ->whereNotNull('employment_start_date')
            ->orderByDesc('employment_start_date')
            ->first();

        // ❌ No qualifying employment
        if (!$employment) {
            return false;
        }

        $dob = Carbon::parse($personal->date_of_birth);
        $employmentStart = Carbon::parse($employment->employment_start_date);

        // Legal working age
        $legalWorkingDate = $dob->copy()->addYears(18);

        // ❌ Employment before legal age → only count from legal age
        $effectiveStart = $employmentStart->lt($legalWorkingDate)
            ? $legalWorkingDate
            : $employmentStart;

        $monthsTrading = $effectiveStart->diffInMonths(now());

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
