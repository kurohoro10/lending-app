<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Class AgeCalculator
 *
 * Provides utility methods for calculating age-related business logic,
 * specifically regarding legal compliance and employment eligibility.
 *
 * @package App\Helpers
 */
class AgeCalculator
{
    /**
     * Get the date the individual reaches legal age (18th birthday).
     *
     * This method takes a Date of Birth and returns the exact date
     * the individual turns 18, used for verifying legal signing capacity.
     *
     * @param Carbon $dob The date of birth to calculate from.
     * @return \Carbon\Carbon The date of the 18th birthday.
     */
    public static function legalAgeDate(Carbon $dob): Carbon
    {
        return $dob->copy()->addYears(18);
    }

    /**
     * Get the effective employment start date.
     *
     * Logic dictates that employment years only count toward professional
     * standing once the individual has reached legal age. If they started
     * working before 18, the effective date is capped at their 18th birthday.
     *
     * @param Carbon $dob             The individual's date of birth.
     * @param Carbon $employmentStart The actual date the individual began employment.
     * @return \Carbon\Carbon The adjusted start date based on legal age requirements.
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
