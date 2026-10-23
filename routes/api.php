<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExtendedProfileController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => response()->json(['message' => 'Verified user']));
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->email_verified_at,
            // Дополнительные данные
            'last_name' => $request->user()->last_name,
            'middle_name' => $request->user()->middle_name,
            'phone' => $request->user()->phone,
            'city' => $request->user()->city,
            'church_name' => $request->user()->church_name,
            'birth_date' => $request->user()->birth_date?->format('Y-m-d'),
            'about' => $request->user()->about,
            'marital_status' => $request->user()->marital_status,
            'gender' => $request->user()->gender,
            'avatar_url' => $request->user()->avatar_url,
            'consent_pd' => $request->user()->consent_pd,
            'can_upload_avatar' => $request->user()->can_upload_avatar,
        ]);
    });

    Route::put('/user/extended-profile', [ExtendedProfileController::class, 'update']);
    Route::post('/user/avatar', [ExtendedProfileController::class, 'uploadAvatar']);
    Route::delete('/user/avatar', [ExtendedProfileController::class, 'destroyAvatar']);
    Route::post('/user/revoke-consent', [ExtendedProfileController::class, 'revokeConsent']);
});
