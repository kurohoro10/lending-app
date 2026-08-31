<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\TwoFactor\EmailOtpCode;
use Illuminate\Support\Facades\Hash;

class GenerateEmailTwoFactorCode
{
    /**
     * Generate a new email OTP code for the given user, persist it hashed,
     * and email the plaintext code to them. The plaintext code is never
     * stored — only its hash.
     */
    public function __invoke(User $user): void
    {
        $length = (int) config('two_factor.email.code_length', 6);
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_otp_code' => Hash::make($code),
            'email_otp_expires_at' => now()->addMinutes((int) config('two_factor.email.expires_after_minutes', 10)),
            'email_otp_attempts' => 0,
        ])->save();

        $user->notify(new EmailOtpCode($code, (int) config('two_factor.email.expires_after_minutes', 10)));
    }
}
