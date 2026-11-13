<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsentPd
{
    /**
     * Пропускает только пользователей, давших согласие на обработку ПДн.
     * Используется на роутах комментариев, лайков комментариев и т.п.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny(
                'Требуется авторизация.',
                'UNAUTHENTICATED',
                401
            );
        }

        if (! $user->consent_pd) {
            return $this->deny(
                'Необходимо согласие на обработку персональных данных.',
                'CONSENT_REQUIRED',
                403
            );
        }

        return $next($request);
    }

    private function deny(string $message, string $code, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'code' => $code,
        ], $status);
    }
}
