<?php

namespace App\Helpers;

use Carbon\Carbon;

class AgeCalculator
{
    /**
     * Get legal age date (18th birthday)
     */
    public static function legalAgeDate(Carbon $dob): Carbon
    {
        return $dob->copy()->addYears(18);
    }

    /**
     * Get effective employment start date
     * Employment years only count after legal age
     */
    public static function effectiveEmploymentStart(
        Carbon $dob,
        Carbon $employmentStart
    ): Carbon {
        $legalAgeDate = self::legalAgeDate($dob);

        return $employmentStart->greaterThan($legalAgeDate)
            ? $employmentStart
            : $legalAgeDate;
    }
}
