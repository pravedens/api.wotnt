<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
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

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            // 1. Генерируем относительный signed URL для API-маршрута
            $apiPath = URL::temporarySignedRoute(
                'api.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $id,
                    'hash' => $hash,
                ],
                absolute: false, // ← относительный URL
            );

            // $apiPath = "/api/email/verify/4/{hash}?expires=...&signature=..."

            // 2. Извлекаем query-строку
            $parsed = parse_url($apiPath);
            $query = $parsed['query'] ?? '';

            // 3. Формируем URL для Nuxt
            $frontendUrl = rtrim(config('app.frontend_url'), '/');

            return "{$frontendUrl}/auth/verify-email/{$id}/{$hash}?{$query}";
        });
    }
}
