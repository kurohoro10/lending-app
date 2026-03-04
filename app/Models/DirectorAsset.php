<?php
// app/Models/DirectorAsset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'asset_type',
        'description',
        'property_use',
        'estimated_value',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getAssetTypeLabelAttribute(): string
    {
        return match($this->asset_type) {
            'house'   => 'House / Property',
            'bank'    => 'Bank Account',
            'super'   => 'Superannuation',
            'vehicle' => 'Vehicle',
            'other'   => 'Other',
            default   => ucfirst($this->asset_type),
        };
    }

    public function getShowPropertyUseAttribute(): bool
    {
        return $this->asset_type === 'house';
    }
}
