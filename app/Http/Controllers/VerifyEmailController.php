<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash)
    {
        // 1. Находим пользователя по ID из ссылки
        $user = User::findOrFail($id);

        // 2. Проверяем, что hash совпадает с email пользователя
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // 3. Если уже подтверждён — возвращаем сообщение
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email уже подтверждён.']);
        }

        // 4. Подтверждаем email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email успешно подтверждён! Можете вернуться на сайт.']);
    }
}
