<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasTeams;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'email',
        'password',
        'failed_login_attempts',  // lockout tracking
        'locked_at',              // lockout tracking
        'two_factor_method',      // 'app' | 'email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'email_otp_code',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'locked_at'                  => 'datetime',   // lockout tracking
            'password'                   => 'hashed',
            'email_two_factor_confirmed_at' => 'datetime',
            'email_otp_expires_at'          => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function assignedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_to');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Helper methods
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isAssessor(): bool
    {
        return $this->hasRole('assessor');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function canAccessAdmin(): bool
    {
        return $this->hasRole(['admin', 'assessor']);
    }

    public function isSystem(): bool
    {
        return $this->hasRole('system');
    }

    /**
     * Whether Authenticator App (TOTP) is this user's confirmed 2FA method.
     */
    public function usesAppTwoFactor(): bool
    {
        return ! empty($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Whether Email OTP is this user's confirmed 2FA method.
     */
    public function usesEmailTwoFactor(): bool
    {
        return $this->two_factor_method === 'email' && ! is_null($this->email_two_factor_confirmed_at);
    }

    /**
     * Whether this account is subject to lockout rules.
     * Admin and system accounts are always exempt.
     */
    public function isLockoutExempt(): bool
    {
        return $this->hasRole(['admin', 'system']);
    }

    /**
     * Whether this account is currently locked.
     */
    public function isLocked(): bool
    {
        if ($this->isLockoutExempt()) {
            return false;
        }

        return $this->locked_at !== null;
    }

    /**
     * Unlock this account and reset failure counter.
     * Useful for admin "unlock user" actions.
     */
    public function unlock(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_at'             => null,
        ]);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Accessor for full display name.
     * Ensures name is always computed from structured fields.
     */
    public function getNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_extension,
        ])->filter()->implode(' ') ?: 'Unnamed User';
    }
}
