<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController; // ✅ Добавлен импорт

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::ignoreRoutes();

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        $this->registerFortifyRoutes();

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');
            return Limit::perMinute(10)->by(($credentialId ?: $request->session()->getId()).'|'.$request->ip());
        });
    }

    protected function registerFortifyRoutes(): void
    {
        // ⚠️ ВАЖНО: все маршруты Fortify (включая 2FA) должны быть в группе 'web',
        // чтобы имели доступ к сессии.
        Route::group([
            'middleware' => config('fortify.middleware', ['web']),
        ], function () {
            // --- Гостевые маршруты ---
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard'), 'throttle:login'])
                ->name('login');

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('register');

            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('password.email');

            Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('password.update');

            // ✅ 2FA challenge (для гостей) — ВНУТРИ группы web
            Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
                ->middleware(['guest:'.config('fortify.guard')])
                ->name('two-factor.login');

            // --- Авторизованные маршруты ---
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware(['auth:'.config('fortify.guard')])
                ->name('logout');

            Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth:'.config('fortify.guard'), 'throttle:6,1'])
                ->name('verification.send');

            // --- Профиль и пароль ---
            Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
                ->middleware(['auth:'.config('fortify.guard')])
                ->name('user-profile-information.update');

            Route::put('/user/password', [PasswordController::class, 'update'])
                ->middleware(['auth:'.config('fortify.guard')])
                ->name('user-password.update');

            Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware(['auth:'.config('fortify.guard')])
                ->name('password.confirm');

            Route::get('/user/confirmed-password-status', [ConfirmablePasswordController::class, 'show'])
                ->middleware(['auth:'.config('fortify.guard')])
                ->name('password.confirmation');

            // --- 2FA (Управление для авторизованных) ---
            Route::middleware(['auth:'.config('fortify.guard')])->group(function () {
                Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
                    ->name('two-factor.enable');
                Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                    ->name('two-factor.disable');
                Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
                    ->name('two-factor.qr-code');
                Route::get('/user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
                    ->name('two-factor.secret-key');
                Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
                    ->name('two-factor.recovery-codes');
                Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
                    ->name('two-factor.regenerate-recovery-codes');
                Route::post('/user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                    ->name('two-factor.confirm');
            });

            // --- ⚠️ Подтверждение email БЕЗ auth ---
            Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');
        });
    }
}
