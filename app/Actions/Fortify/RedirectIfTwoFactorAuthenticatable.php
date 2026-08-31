<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyRedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Extends Fortify's own redirect-decision action so email OTP users are
 * routed into the same pending-two-factor challenge as TOTP users.
 *
 * validateCredentials() and twoFactorChallengeResponse() are inherited
 * unchanged from the parent (both are `protected`, not `private`) — only
 * the decision branch inside handle() is reimplemented, since Fortify does
 * not expose a finer-grained hook to add a third condition to it.
 */
class RedirectIfTwoFactorAuthenticatable extends FortifyRedirectIfTwoFactorAuthenticatable
{
    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if (optional($user)->usesEmailTwoFactor()) {
            app(GenerateEmailTwoFactorCode::class)($user);

            return $this->twoFactorChallengeResponse($request, $user);
        }

        // Unchanged vendor TOTP / passthrough logic.
        if (Fortify::confirmsTwoFactorAuthentication()) {
            if (optional($user)->two_factor_secret &&
                ! is_null(optional($user)->two_factor_confirmed_at) &&
                in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
                return $this->twoFactorChallengeResponse($request, $user);
            }

            return $next($request);
        }

        if (optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }
}
