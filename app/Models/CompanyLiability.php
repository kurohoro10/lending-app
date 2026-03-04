<?php
// app/Models/CompanyLiability.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyLiability extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'liability_name',
        'notes',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
