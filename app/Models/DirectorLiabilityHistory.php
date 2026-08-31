<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorLiabilityHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'director_liability_id',
        'changed_by',
        'field',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function liability(): BelongsTo
    {
        return $this->belongsTo(DirectorLiability::class, 'director_liability_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFieldLabelAttribute(): string
    {
        return match($this->field) {
            'liability_type'      => 'Liability Type',
            'lender_name'         => 'Lender Name',
            'credit_limit'        => 'Credit Limit',
            'outstanding_balance' => 'Outstanding Balance',
            default               => ucfirst(str_replace('_', ' ', $this->field)),
        };
    }
}