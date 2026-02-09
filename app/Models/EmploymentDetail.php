<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'employment_type',
        'employer_business_name',
        'abn',
        'employment_role',
        'position',
        'employment_start_date',
        'length_of_employment_months',
        'base_income',
        'additional_income',
        'income_frequency',
        'employer_phone',
        'employer_address',
    ];

    protected $casts = [
        'employment_start_date' => 'date',
        'length_of_employment_months' => 'integer',
        'base_income' => 'decimal:2',
        'additional_income' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getAnnualIncome(): float
    {
        $totalIncome = (float)$this->base_income + (float)$this->additional_income;

        return match($this->income_frequency) {
            'weekly' => $totalIncome * 52,
            'fortnightly' => $totalIncome * 26,
            'monthly' => $totalIncome * 12,
            'annual' => $totalIncome,
            default => 0,
        };
    }

    public function getMonthlyIncome(): float
    {
        return $this->getAnnualIncome() / 12;
    }

    public function calculateEmploymentLength(): void
    {
        if ($this->employment_start_date) {
            $this->length_of_employment_months = $this->employment_start_date->diffInMonths(now());
            $this->save();
        }
    }
}
