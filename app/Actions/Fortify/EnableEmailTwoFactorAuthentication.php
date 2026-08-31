<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

class EnableEmailTwoFactorAuthentication
{
    /**
     * Mark email two factor authentication as the user's confirmed method.
     *
     * Shared by the profile's manual "confirm code" flow and the automatic
     * enablement triggered on first email verification, so both paths apply
     * the same mutual-exclusivity rule (only one method may be active).
     */
    public function __invoke(User $user): void
    {
        $user->forceFill([
            'two_factor_method' => 'email',
            'email_two_factor_confirmed_at' => now(),
        ])->save();

        if ($user->two_factor_secret) {
            app(DisableTwoFactorAuthentication::class)($user);
        }
    }
}
