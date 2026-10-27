<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExtendedProfileController;
use App\Http\Resources\UserResource;

// --- Группа 1: Все авторизованные (включая неподтверждённых) ---
Route::middleware(['auth:sanctum'])->group(function () {
    // ✅ Dashboard доступен всем, т.к. там висит плашка "Подтвердите почту"
    Route::get('/dashboard', fn () => response()->json(['message' => 'Authenticated user']));

    // ✅ /user без verified, чтобы фронтенд знал статус верификации
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
});

// --- Группа 2: Только подтверждённые пользователи ---
Route::middleware(['auth:sanctum', 'verified', 'throttle:10,1'])->group(function () {
    // ✅ Маршруты профиля защищены, т.к. туда пускаем только после подтверждения
    Route::put('/user/extended-profile', [ExtendedProfileController::class, 'update']);
    Route::post('/user/avatar', [ExtendedProfileController::class, 'uploadAvatar']);
    Route::delete('/user/avatar', [ExtendedProfileController::class, 'destroyAvatar']);
    Route::post('/user/revoke-consent', [ExtendedProfileController::class, 'revokeConsent']);
});
