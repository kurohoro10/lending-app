<?php

namespace App\Providers;

use App\Livewire\Profile\TwoFactorAuthenticationForm;
use App\Models\Application;
use App\Policies\ApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Events\Application\ApplicationReturned;
use App\Events\Application\ApplicationStatusChanged;
use App\Listeners\Application\SendReturnNotifications;
use App\Listeners\Application\SendStatusChangeNotifications;
use App\Listeners\Auth\EnableEmailTwoFactorOnVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
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
        Gate::policy(Application::class, ApplicationPolicy::class);

        // Grant all permissions to 'admin' role
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        Event::listen(
            ApplicationStatusChanged::class,
            SendStatusChangeNotifications::class,
        );

        Event::listen(
            ApplicationReturned::class,
            SendReturnNotifications::class,
        );

        // First-time email verification auto-enables Email OTP as the
        // user's 2FA method, unless they already have Authenticator App
        // (or Email OTP) active — see App\Listeners\Auth\EnableEmailTwoFactorOnVerification.
        Event::listen(
            Verified::class,
            EnableEmailTwoFactorOnVerification::class,
        );

        \Illuminate\Support\Facades\Blade::directive('formatDate', function ($expression) {
            return "<?php echo \App\Helpers\DateFormatter::datetime($expression); ?>";
        });

        // Override Jetstream's profile 2FA component with our own, which extends
        // it to add the Email OTP method alongside the unmodified TOTP flow.
        // Deferred to `booted()` so this registration always wins over
        // Jetstream's own `Livewire::component(...)` call, regardless of
        // service provider boot order.
        $this->app->booted(function () {
            Livewire::component('profile.two-factor-authentication-form', TwoFactorAuthenticationForm::class);
        });
    }
}
