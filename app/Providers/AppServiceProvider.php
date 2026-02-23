<?php

namespace App\Providers;

use App\Models\Application;
use App\Policies\ApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Events\Application\ApplicationReturned;
use App\Events\Application\ApplicationStatusChanged;
use App\Listeners\Application\SendReturnNotifications;
use App\Listeners\Application\SendStatusChangeNotifications;
use Illuminate\Support\Facades\Event;

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
    }
}
