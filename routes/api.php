<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExtendedProfileController;
use App\Http\Resources\UserResource;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => response()->json(['message' => 'Verified user']));
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::put('/user/extended-profile', [ExtendedProfileController::class, 'update']);
    Route::post('/user/avatar', [ExtendedProfileController::class, 'uploadAvatar']);
    Route::delete('/user/avatar', [ExtendedProfileController::class, 'destroyAvatar']);
    Route::post('/user/revoke-consent', [ExtendedProfileController::class, 'revokeConsent']);
});
