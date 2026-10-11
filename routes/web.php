<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
        'remember' => ['sometimes', 'boolean'],
    ]);

    $authenticated = Auth::guard('web')->attempt(
        [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ],
        $request->boolean('remember'),
    );

    if (! $authenticated) {
        throw ValidationException::withMessages([
            'email' => ['Неверный email или пароль.'],
        ]);
    }

    $request->session()->regenerate();

    return response()->noContent();
})->middleware('throttle:5,1');
