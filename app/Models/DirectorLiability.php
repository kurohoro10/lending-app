<?php
// app/Models/DirectorLiability.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorLiability extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'liability_type',
        'lender_name',
        'credit_limit',
        'outstanding_balance',
    ];

    protected $casts = [
        'credit_limit'        => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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
}
