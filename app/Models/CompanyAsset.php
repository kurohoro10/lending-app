<?php
// app/Models/CompanyAsset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'asset_name',
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
