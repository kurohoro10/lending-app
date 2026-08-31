<?php
// app/Models/DirectorAsset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectorAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'asset_type',
        'description',
        'property_use',
        'estimated_value',
        'is_owned',
        'ownership_percentage',
        'comment',
        'name',
    ];

    protected $casts = [
        'estimated_value'      => 'decimal:2',
        'is_owned'             => 'boolean',
        'ownership_percentage' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(DirectorAssetHistory::class)
                    ->orderBy('changed_at', 'desc');
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

    protected static function booted(): void
    {
        static::updating(function (DirectorAsset $asset) {
            $trackFields = [
                'asset_type', 'description', 'property_use',
                'estimated_value', 'is_owned', 'ownership_percentage',
            ];

            foreach ($trackFields as $field) {
                if ($asset->isDirty($field)) {
                    DirectorAssetHistory::create([
                        'director_asset_id' => $asset->id,
                        'changed_by'        => auth()->id(),
                        'field'             => $field,
                        'old_value'         => $asset->getOriginal($field),
                        'new_value'         => $asset->getAttribute($field),
                    ]);
                }
            }
        });
    }
}