<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessorDalVerification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'initiated_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    // Who clicked "Allow Assessor to Edit"
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    // Who last clicked "Save & Verify"
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // Whether the stamp has been applied
    public function isStamped(): bool
    {
        return $this->verified_at !== null && $this->verified_by !== null;
    }
}