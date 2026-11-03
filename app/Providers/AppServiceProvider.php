<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passkeys\Events\PasskeyRegistered;

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
            // ✅ Используем config, а не хардкод
            $frontendUrl = rtrim(config('app.frontend_url'), '/');

            return $frontendUrl.'/auth/reset-password?'.http_build_query([
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

            // Подписываем полный URL маршрута Laravel.
            $apiUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(
                    Config::get('auth.verification.expire', 60)
                ),
                [
                    'id' => $id,
                    'hash' => $hash,
                ],
                absolute: true,
            );

            // Передаём параметры подписанного API URL на страницу Nuxt.
            $query = parse_url($apiUrl, PHP_URL_QUERY);

            $frontendUrl = rtrim(config('app.frontend_url'), '/');

            return "{$frontendUrl}/auth/verify-email/{$id}/{$hash}?{$query}";
        });

        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        Event::listen(PasskeyRegistered::class, function ($event) {
            $user = $event->passkey->user;

            // Если у пользователя была включена 2FA — отключаем её
            if ($user->two_factor_secret) {
                $user->forceFill([
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                ])->save();
            }
        });
    }
}
