<?php

namespace App\Http\Requests;

use App\Actions\Fortify\VerifyEmailTwoFactorCode;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest as FortifyTwoFactorLoginRequest;

/**
 * Extends Fortify's own challenge request so that Fortify's stock
 * TwoFactorAuthenticatedSessionController::store() transparently accepts
 * either a TOTP code or an email OTP code, depending on the challenged
 * user's chosen method — without any change to the controller or route.
 *
 * validRecoveryCode(), challengedUser(), remember() are all inherited
 * unchanged, so TOTP recovery codes keep working exactly as before.
 */
class TwoFactorLoginRequest extends FortifyTwoFactorLoginRequest
{
    public function hasValidCode()
    {
        $user = $this->challengedUser();

        if ($user->usesEmailTwoFactor()) {
            return app(VerifyEmailTwoFactorCode::class)($user, $this->code, function () {
                $this->session()->forget('login.id');
            });
        }

        return parent::hasValidCode();
    }
}
