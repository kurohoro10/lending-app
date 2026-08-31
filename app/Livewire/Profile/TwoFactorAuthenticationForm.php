<?php

namespace App\Livewire\Profile;

use App\Actions\Fortify\EnableEmailTwoFactorAuthentication;
use App\Actions\Fortify\GenerateEmailTwoFactorCode;
use App\Actions\Fortify\VerifyEmailTwoFactorCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm as JetstreamTwoFactorAuthenticationForm;

/**
 * Extends Jetstream's own Livewire component so the existing Authenticator
 * App (TOTP) flow — enable/confirm/disable/regenerate recovery codes — is
 * inherited completely unchanged. Only the method selector and the new
 * Email OTP setup flow are added on top.
 *
 * Only one method can be the confirmed/active second factor at a time:
 * confirming one clears the other's confirmed state, so the login-time
 * decision (App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable) always
 * has a single, unambiguous method to challenge against.
 */
class TwoFactorAuthenticationForm extends JetstreamTwoFactorAuthenticationForm
{
    /**
     * The currently selected method in the UI ('app' or 'email').
     * Purely a display toggle until the user actually confirms a method.
     */
    public string $method = 'app';

    /**
     * Indicates if the email OTP confirmation code input is being displayed.
     */
    public bool $showingEmailCode = false;

    /**
     * The OTP code for confirming email two factor authentication.
     */
    public ?string $emailCode = null;

    public function mount()
    {
        parent::mount();

        // Mirrors the vendor's own guard against an abandoned TOTP setup,
        // applied to an abandoned Email OTP setup.
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm') &&
            Auth::user()->two_factor_method === 'email' &&
            is_null(Auth::user()->email_two_factor_confirmed_at)) {
            Auth::user()->forceFill(['two_factor_method' => null])->save();
        }

        $this->method = $this->user->usesEmailTwoFactor() ? 'email' : 'app';
    }

    /**
     * Determine if email two factor authentication is enabled.
     */
    public function getEmailEnabledProperty(): bool
    {
        return $this->user->usesEmailTwoFactor();
    }

    /**
     * Send (or resend) the email OTP code for confirming email two factor authentication.
     */
    public function sendEmailTwoFactorCode(GenerateEmailTwoFactorCode $generate)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $key = 'email-otp-setup:'.$this->user->getKey();
        $seconds = (int) config('two_factor.email.resend_throttle_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $this->addError('emailCode', 'Please wait a moment before requesting another code.');

            return;
        }

        RateLimiter::hit($key, $seconds);

        $generate($this->user);

        $this->showingEmailCode = true;
    }

    /**
     * Confirm email two factor authentication for the user.
     */
    public function confirmEmailTwoFactorAuthentication(VerifyEmailTwoFactorCode $verify, EnableEmailTwoFactorAuthentication $enable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        if (! $verify($this->user, $this->emailCode, fn () => null)) {
            $this->addError('emailCode', 'The provided code was invalid.');

            return;
        }

        $enable($this->user);

        $this->showingEmailCode = false;
        $this->emailCode = null;
    }

    /**
     * Disable email two factor authentication for the user.
     */
    public function disableEmailTwoFactorAuthentication()
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $this->user->forceFill([
            'two_factor_method' => null,
            'email_two_factor_confirmed_at' => null,
            'email_otp_code' => null,
            'email_otp_expires_at' => null,
            'email_otp_attempts' => 0,
        ])->save();

        $this->showingEmailCode = false;
        $this->emailCode = null;
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication(ConfirmTwoFactorAuthentication $confirm)
    {
        parent::confirmTwoFactorAuthentication($confirm);

        $this->user->forceFill(['two_factor_method' => 'app'])->save();

        // Only one method may be active at a time.
        if ($this->user->usesEmailTwoFactor()) {
            $this->user->forceFill([
                'two_factor_method' => 'app',
                'email_two_factor_confirmed_at' => null,
                'email_otp_code' => null,
                'email_otp_expires_at' => null,
                'email_otp_attempts' => 0,
            ])->save();
        }
    }

    public function render()
    {
        return view('profile.two-factor-authentication-form');
    }
}
