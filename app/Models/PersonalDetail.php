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
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'mobile_phone',
        'email',
        'marital_status',
        'number_of_dependants',
        'spouse_name',
        'spouse_income',
        'date_of_birth',
        'gender',
        'citizenship_status',
        'contact_role',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'number_of_dependants' => 'integer',
        'spouse_income'        => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_extension,
        ])->filter()->implode(' ');
    }

    /**
     * Whether additional address history should be collected
     * (less than 12 months at current address).
     */
    public function getNeedsAddressHistoryAttribute(): bool
    {
        return $this->months_at_address !== null && $this->months_at_address < 12;
    }
}
