<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->email_verified_at,
        ]);
    });
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => response()->json(['message' => 'Verified user']));
});
