<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyEmailTwoFactorCode
{
    /**
     * Verify a submitted email OTP code for the given user.
     *
     * On success, the code is invalidated (one-time use) and the given
     * $onSuccess callback is invoked (used to forget the pending-login
     * session state the same way Fortify's own TOTP check does).
     *
     * On failure, the attempt counter is incremented; once it reaches the
     * configured maximum the code is invalidated, forcing the user to
     * request a new one.
     */
    public function __invoke(User $user, ?string $code, callable $onSuccess): bool
    {
        $maxAttempts = (int) config('two_factor.email.max_attempts', 5);

        if (
            ! $code ||
            ! $user->email_otp_code ||
            $user->email_otp_attempts >= $maxAttempts ||
            ! $user->email_otp_expires_at ||
            $user->email_otp_expires_at->isPast()
        ) {
            return false;
        }

        if (! Hash::check($code, $user->email_otp_code)) {
            $attempts = $user->email_otp_attempts + 1;

            $user->forceFill([
                'email_otp_attempts' => $attempts,
                'email_otp_code' => $attempts >= $maxAttempts ? null : $user->email_otp_code,
                'email_otp_expires_at' => $attempts >= $maxAttempts ? null : $user->email_otp_expires_at,
            ])->save();

            return false;
        }

        $user->forceFill([
            'email_otp_code' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
        ])->save();

        $onSuccess();

        return true;
    }
}
