<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentialAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'address_type',
        'street_address',
        'suburb',
        'state',
        'postcode',
        'country',
        'start_date',
        'end_date',
        'months_at_address',
        'residential_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'months_at_address' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getFullAddressAttribute(): string
    {
        return sprintf(
            '%s, %s, %s %s, %s',
            $this->street_address,
            $this->suburb,
            $this->state,
            $this->postcode,
            $this->country
        );
    }

    public function calculateMonthsAtAddress(): void
    {
        if ($this->start_date) {
            $endDate = $this->end_date ?? now();
            $this->months_at_address = $this->start_date->diffInMonths($endDate);
            $this->save();
        }
    }
}
