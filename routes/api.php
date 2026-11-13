<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\ExtendedProfileController;
use App\Http\Controllers\HomepageController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/homepage', [HomepageController::class, 'index']);

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

Route::get('/categories', CategoryController::class);
Route::get('/groups', GroupController::class);
Route::get('/conferences', ConferenceController::class);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/latest', [PostController::class, 'latest']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);
Route::post('/posts/{post}/view', [PostController::class, 'registerView']);

Route::middleware(['auth:sanctum', 'verified', 'consent'])->group(function () {
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{post}/favorite', [PostController::class, 'toggleFavorite']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'toggleLike']);
    Route::get('/user/favorites', [PostController::class, 'favorites']);
});
