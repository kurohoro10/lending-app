<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateUser;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\ClientVerifyEmailResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest as FortifyTwoFactorLoginRequest;
use App\Http\Requests\TwoFactorLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Register custom authentication action with lockout logic
        Fortify::authenticateUsing(app(AuthenticateUser::class));

        // Lets the email-OTP method be verified through Fortify's own
        // /two-factor-challenge route/controller, transparently alongside TOTP.
        $this->app->bind(FortifyTwoFactorLoginRequest::class, TwoFactorLoginRequest::class);

        $this->app->singleton(
            VerifyEmailResponseContract::class,
            ClientVerifyEmailResponse::class
        );

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('email-otp-resend', function (Request $request) {
            $seconds = (int) config('two_factor.email.resend_throttle_seconds', 60);

            return Limit::perMinutes(max(1, (int) ceil($seconds / 60)), 1)
                ->by('email-otp-resend|'.$request->session()->get('login.id'));
        });

        View::composer('auth.two-factor-challenge', function ($view) {
            $user = User::find(session('login.id'));

            $view->with('challengedUserUsesEmail', optional($user)->usesEmailTwoFactor() ?? false);
            $view->with('challengedUserEmail', optional($user)->email);
        });
    }
}
