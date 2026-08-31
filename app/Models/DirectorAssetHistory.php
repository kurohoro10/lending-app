<?php
// app/Models/DirectorAssetHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorAssetHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'director_asset_id',
        'changed_by',
        'field',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(DirectorAsset::class, 'director_asset_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFieldLabelAttribute(): string
    {
        return match($this->field) {
            'asset_type'      => 'Asset Type',
            'description'     => 'Description',
            'property_use'    => 'Property Use',
            'estimated_value' => 'Estimated Value',
            default           => ucfirst(str_replace('_', ' ', $this->field)),
        };
    }
}