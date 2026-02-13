<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'full_name',
        'mobile_phone',
        'email',
        'marital_status',
        'number_of_dependants',
        'spouse_name',
        'date_of_birth',
        'gender',
        'citizenship_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'number_of_dependants' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
}
