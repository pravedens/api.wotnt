<?php

namespace App\Http\Requests;

use App\Models\PostComment;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostCommentRequest extends FormRequest
{
    /**
     * Редактировать комментарий может только его автор
     * (и только если он дал согласие на обработку ПДн).
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        /** @var PostComment|null $comment */
        $comment = $this->route('comment');

        if (! $comment) {
            return false;
        }

        return $user->id === $comment->user_id
            && (bool) $user->consent_pd;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:2000'],
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
        ];
    }

    /**
     * Кастомный ответ при провале авторизации.
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

        /** @var PostComment|null $comment */
        $comment = $this->route('comment');

        if (! $comment || $comment->user_id !== $user->id) {
            abort(response()->json([
                'message' => 'Вы можете редактировать только свои комментарии.',
                'code' => 'FORBIDDEN',
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
