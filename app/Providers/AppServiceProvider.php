<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return 'https://wotnt.test:8443/auth/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        if ($this->app->environment('local')) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
