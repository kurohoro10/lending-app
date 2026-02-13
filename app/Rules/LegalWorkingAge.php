<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;

class LegalWorkingAge implements ValidationRule
{
    protected $dob;

    public function __construct($dob)
    {
        $this->dob = $dob;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->dob) {
            $fail('Date of birth must be provided before adding employment details.');
            return;
        }

        $legalAgeDate = Carbon::parse($this->dob)->addYears(18);

        if (Carbon::parse($value)->lt($legalAgeDate)) {
            $fail('Employment cannot start before legal working age (18).');
        }
    }
}

