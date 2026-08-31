<?php
// app/Models/BorrowerDirector.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BorrowerDirector extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'ownership_percentage',
        'is_guarantor',
    ];

    protected $casts = [
        'date_of_birth'        => 'date',
        'ownership_percentage' => 'decimal:2',
        'is_guarantor'         => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function livingExpenses(): HasOne
    {
        return $this->hasOne(DirectorLivingExpense::class);
    }

    public function hasSubmittedExpenses(): bool
    {
        return $this->livingExpenses()->exists();
    }
}
