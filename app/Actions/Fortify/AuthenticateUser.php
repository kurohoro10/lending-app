<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    /**
     * Roles that are EXEMPT from the lockout rule.
     */
    protected const EXEMPT_ROLES = ['admin', 'system'];

    /**
     * Maximum failed attempts before locking a non-exempt account.
     */
    protected const MAX_ATTEMPTS = 2;

    /**
     * Called via Fortify::authenticateUsing().
     * Must return the authenticated User model, or null on failure.
     * Throws ValidationException to surface friendly error messages.
     */
    public function __invoke(Request $request): ?User
    {
        /** @var User|null $user */
        $user = User::where(
            Fortify::username(),
            $request->input(Fortify::username())
        )->first();

        // ── 1. Unknown email — generic error (no info leakage) ───────────────
        if (! $user) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        // ── 2. Locked account check (non-exempt only) ─────────────────────────
        if (! $this->isExempt($user) && $user->locked_at !== null) {
            throw ValidationException::withMessages([
                Fortify::username() => [
                    'Your account has been locked due to too many failed login attempts. '
                    . 'Please contact support to unlock your account.',
                ],
            ]);
        }

        // ── 3. Verify password ────────────────────────────────────────────────
        if (! Hash::check($request->input('password'), $user->password)) {

            // Only track & warn for non-exempt accounts
            if (! $this->isExempt($user)) {
                $this->incrementFailures($user);
                $fresh = $user->fresh();

                // Just got locked on this attempt
                if ($fresh->locked_at !== null) {
                    throw ValidationException::withMessages([
                        Fortify::username() => [
                            'Your account has been locked due to too many failed login attempts. '
                            . 'Please contact support to unlock your account.',
                        ],
                    ]);
                }

                $remaining = self::MAX_ATTEMPTS - $fresh->failed_login_attempts;

                throw ValidationException::withMessages([
                    Fortify::username() => [
                        'These credentials do not match our records. '
                        . "{$remaining} attempt(s) remaining before your account is locked.",
                    ],
                ]);
            }

            // Exempt role — generic error, no attempt tracking
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        // ── 4. Successful login — reset failure counter ───────────────────────
        if (! $this->isExempt($user)) {
            $user->update([
                'failed_login_attempts' => 0,
                'locked_at'             => null,
            ]);
        }

        return $user;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function isExempt(User $user): bool
    {
        return $user->hasRole(self::EXEMPT_ROLES);
    }

    private function incrementFailures(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;

        $user->update([
            'failed_login_attempts' => $attempts,
            'locked_at'             => $attempts >= self::MAX_ATTEMPTS ? now() : null,
        ]);
    }
}