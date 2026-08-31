<?php

namespace App\Listeners\Auth;

use App\Actions\Fortify\EnableEmailTwoFactorAuthentication;
use Illuminate\Auth\Events\Verified;

/**
 * Fortify's VerifyEmailController fires Verified only the first time a
 * user's email is confirmed (it short-circuits on repeat verification), so
 * this always represents a first-time verification — no extra guard needed
 * for that. Skips users who already have an active 2FA method so an
 * existing Authenticator App setup is never silently replaced, and so this
 * listener stays a no-op if it somehow ran twice for the same user.
 */
class EnableEmailTwoFactorOnVerification
{
    public function __construct(private EnableEmailTwoFactorAuthentication $enable)
    {
    }

    public function handle(Verified $event): void
    {
        $user = $event->user;

        if ($user->usesAppTwoFactor() || $user->usesEmailTwoFactor()) {
            return;
        }

        ($this->enable)($user);
    }
}
