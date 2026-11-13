<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    /**
     * Разрешаем запрос только авторизованным пользователям
     * с подтверждённым email и согласием на обработку ПДн.
     * Дополнительно проверяется middleware 'consent' на роуте.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasVerifiedEmail()
            && (bool) $user->consent_pd;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'parent_id' => [
                'nullable',
                'integer',
                // Родительский комментарий должен существовать
                // и принадлежать тому же посту, что и в URL.
                function ($attribute, $value, $fail) {
                    if ($value === null) {
                        return;
                    }

                    $post = $this->route('post');

                    $exists = \App\Models\PostComment::whereKey($value)
                        ->where('post_id', $post?->id)
                        ->where('is_approved', true)
                        ->exists();

                    if (! $exists) {
                        $fail('Родительский комментарий не найден.');
                    }
                },
            ],
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Введите текст комментария.',
            'content.min' => 'Комментарий не может быть пустым.',
            'content.max' => 'Комментарий не должен превышать 2000 символов.',
            'parent_id.integer' => 'Некорректный идентификатор родительского комментария.',
        ];
    }

    /**
     * Кастомный ответ при провале авторизации.
     * Фронт получит code: CONSENT_REQUIRED и сможет показать модалку.
     */
    protected function failedAuthorization(): void
    {
        $user = $this->user();

        if (! $user) {
            abort(response()->json([
                'message' => 'Требуется авторизация.',
                'code' => 'UNAUTHENTICATED',
            ], 401));
        }

        if (! $user->hasVerifiedEmail()) {
            abort(response()->json([
                'message' => 'Необходимо подтвердить email.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403));
        }

        if (! $user->consent_pd) {
            abort(response()->json([
                'message' => 'Необходимо согласие на обработку персональных данных.',
                'code' => 'CONSENT_REQUIRED',
            ], 403));
        }

        abort(403);
    }
}
