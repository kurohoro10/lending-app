<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectorLiability extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'liability_type',
        'lender_name',
        'credit_limit',
        'outstanding_balance',
        'monthly_repayment',
        'comment',
        'name', 
    ];

    protected $casts = [
        'credit_limit'        => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'monthly_repayment'   => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(DirectorLiabilityHistory::class)
                    ->orderBy('changed_at', 'desc');
    }

    public function getLiabilityTypeLabelAttribute(): string
    {
        return match($this->liability_type) {
            'credit_card' => 'Credit Card',
            'home_loan'   => 'Home Loan',
            'car_loan'    => 'Car Loan',
            'other'       => 'Other',
            default       => ucfirst($this->liability_type),
        };
    }

    public function getShowCreditLimitAttribute(): bool
    {
        return $this->liability_type === 'credit_card';
    }

    protected static function booted(): void
    {
        static::updating(function (DirectorLiability $liability) {
            $trackFields = [
                'liability_type', 'lender_name',
                'credit_limit', 'outstanding_balance',
            ];

            foreach ($trackFields as $field) {
                if ($liability->isDirty($field)) {
                    DirectorLiabilityHistory::create([
                        'director_liability_id' => $liability->id,
                        'changed_by'            => auth()->id(),
                        'field'                 => $field,
                        'old_value'             => $liability->getOriginal($field),
                        'new_value'             => $liability->getAttribute($field),
                    ]);
                }
            }
        });
    }
}