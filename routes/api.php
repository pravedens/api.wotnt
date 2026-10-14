<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->email_verified_at,
        ]);
    });

    Route::post('/logout', function (Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    });
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => response()->json(['message' => 'Verified user']));
});

if (Features::enabled(Features::emailVerification())) {
    Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
        $user = User::findOrFail($id);

        // 1. Проверяем подпись (relative, без хоста)
        if (! $request->hasValidSignatureWhileIgnoring([], absolute: false)) {
            abort(403, 'Invalid or expired verification link.');
        }

        // 2. Проверяем hash (это sha1 от email)
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification hash.');
        }

        // 3. Подтверждаем email
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified successfully.']);
    })
        ->middleware(['throttle:6,1']) // auth:sanctum не нужен — signed URL сам по себе защищает
        ->name('api.verification.verify');
}
