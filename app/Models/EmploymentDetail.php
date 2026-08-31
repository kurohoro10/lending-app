<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'added_by',
        'employment_type',
        'employer_business_name',
        'abn',
        'employment_role',
        'position',
        'employment_start_date',
        'length_of_employment_months',
        'base_income',
        'after_tax_income',
        'additional_income',
        'income_frequency',
        'employer_phone',
        'employer_address',
        'comment',
        'is_current',
        'employment_end_date',
    ];

    protected $casts = [
        'employment_start_date'       => 'date',
        'length_of_employment_months' => 'integer',
        'base_income'                 => 'decimal:2',
        'after_tax_income'            => 'decimal:2',
        'additional_income'           => 'decimal:2',
        'is_current'                  => 'boolean',
        'employment_end_date'         => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmploymentDetailHistory::class)
                    ->orderBy('changed_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssessorEmploymentDocument::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAssessorAdded(): bool
    {
        return $this->added_by !== null;
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->employment_type));
    }

    public function getAnnualIncome(): float
    {
        $total = (float) $this->base_income + (float) $this->additional_income;

        return match($this->income_frequency) {
            'weekly'      => $total * 52,
            'fortnightly' => $total * 26,
            'monthly'     => $total * 12,
            'annual'      => $total,
            default       => 0,
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

    // ── Auto-history on update ────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::updating(function (EmploymentDetail $employment) {
            $trackFields = [
                'employment_type', 'employer_business_name', 'abn',
                'employment_role', 'position', 'employment_start_date',
                'length_of_employment_months', 'base_income', 'additional_income',
                'income_frequency', 'employer_phone', 'employer_address', 'comment',
            ];

            foreach ($trackFields as $field) {
                if ($employment->isDirty($field)) {
                    EmploymentDetailHistory::create([
                        'employment_detail_id' => $employment->id,
                        'changed_by'           => auth()->id(),
                        'field'                => $field,
                        'old_value'            => $employment->getOriginal($field),
                        'new_value'            => $employment->getAttribute($field),
                    ]);
                }
            }
        });
    }

    public function getDisplayAnnualIncome(): float
    {
        $baseAmount = (float) ($this->after_tax_income ?? $this->base_income);
        $total = $baseAmount + (float) $this->additional_income;

        return match($this->income_frequency) {
            'weekly'      => $total * 52,
            'fortnightly' => $total * 26,
            'monthly'     => $total * 12,
            'annual'      => $total,
            default       => 0,
        };
    }

    public static function calculateAfterTaxIncome(float $grossIncome): array
    {
        // Calculate Income Tax
        if ($grossIncome <= 18200) {
            $incomeTax = 0;
        }
        elseif ($grossIncome <= 45000) {
            $incomeTax = ($grossIncome - 18200) * 0.16;
        }
        elseif ($grossIncome <= 135000) {
            $incomeTax = 4288 + (($grossIncome - 45000) * 0.30);
        }
        elseif ($grossIncome <= 190000) {
            $incomeTax = 31288 + (($grossIncome - 135000) * 0.37);
        }
        else {
            $incomeTax = 51638 + (($grossIncome - 190000) * 0.45);
        }

        // Medicare Levy (2%)
        $medicareLevy = $grossIncome * 0.02;

        // Total Tax
        $totalTax = $incomeTax + $medicareLevy;

        // After Tax Income
        $afterTaxIncome = $grossIncome - $totalTax;

        return [
            'gross_income'      => round($grossIncome, 2),
            'income_tax'        => round($incomeTax, 2),
            'medicare_levy'     => round($medicareLevy, 2),
            'total_tax'         => round($totalTax, 2),
            'after_tax_income'  => round($afterTaxIncome, 2),
        ];
    }

    public static function calculateAfterTaxIncomeForFrequency(float $amount, string $frequency): array
    {
        $multipliers = [
            'weekly'      => 52,
            'fortnightly' => 26,
            'monthly'     => 12,
            'annual'      => 1,
        ];

        $annualMultiplier = $multipliers[$frequency] ?? 1;
        $annualGross      = $amount * $annualMultiplier;

        $result = self::calculateAfterTaxIncome($annualGross);

        $result['after_tax_income_per_period'] = round($result['after_tax_income'] / $annualMultiplier, 2);
        $result['frequency'] = $frequency;

        return $result;
    }

    public function getMonthlyAfterTaxIncome(): float
    {
        $income = $this->after_tax_income ?? $this->base_income;
        
        return match($this->income_frequency) {
            'weekly'      => round(($income * 52) / 12, 2),
            'fortnightly' => round(($income * 26) / 12, 2),
            'monthly'     => round($income, 2),
            'annual'      => round($income / 12, 2),
            default       => 0,
        };
    }
}