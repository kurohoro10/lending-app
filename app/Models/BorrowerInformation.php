<?php
// app/Models/BorrowerInformation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowerInformation extends Model
{
    use HasFactory;

    protected $table = 'borrower_information';

    protected $fillable = [
        'application_id',
        'borrower_name',
        'borrower_type',
        'abn',
        'nature_of_business',
        'years_in_business',
    ];

    protected $casts = [
        'years_in_business' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Formatted borrower type label for display.
     */
    public function getBorrowerTypeLabelAttribute(): string
    {
        return match($this->borrower_type) {
            'individual' => 'Individual',
            'company'    => 'Company',
            'trust'      => 'Trust',
            'other'      => 'Other',
            default      => ucfirst($this->borrower_type),
        };
    }

    /**
     * Formatted ABN with spaces: XX XXX XXX XXX
     */
    public function getFormattedAbnAttribute(): ?string
    {
        if (!$this->abn) return null;
        $clean = preg_replace('/\D/', '', $this->abn);
        return strlen($clean) === 11
            ? substr($clean, 0, 2) . ' ' . substr($clean, 2, 3) . ' ' . substr($clean, 5, 3) . ' ' . substr($clean, 8, 3)
            : $this->abn;
    }
}
