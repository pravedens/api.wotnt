<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExtendedProfileController extends Controller
{
    /**
     * Обновление текстовых данных профиля.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Если пользователь еще не дал согласие, оно обязательно
        if (! $user->consent_pd) {
            $request->validate([
                'consent_pd' => ['required', 'accepted'],
            ], [
                'consent_pd.required' => 'Необходимо согласие на обработку персональных данных.',
                'consent_pd.accepted' => 'Необходимо согласие на обработку персональных данных.',
            ]);
        }

        $validated = $request->validate([
            'last_name' => ['nullable', 'string', 'max:100', 'regex:/^[А-Яа-яЁёA-Za-z\s\-]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[А-Яа-яЁёA-Za-z\s\-]+$/u'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'city' => ['nullable', 'string', 'max:100'],
            'church_name' => ['nullable', 'string', 'max:150'],
            'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'about' => ['nullable', 'string', 'max:1000'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
        ], [
            'last_name.regex' => 'Фамилия может содержать только буквы, пробел и дефис.',
            'middle_name.regex' => 'Отчество может содержать только буквы, пробел и дефис.',
            'phone.regex' => 'Введите корректный номер телефона.',
            'birth_date.before' => 'Дата рождения не может быть в будущем.',
            'about.max' => 'Текст «О себе» не должен превышать 1000 символов.',
        ]);

        // Фиксируем согласие (если дано впервые)
        if ($request->boolean('consent_pd') && ! $user->consent_pd) {
            $validated['consent_pd'] = true;
            $validated['consent_pd_at'] = now();
            $validated['consent_pd_ip'] = $request->ip();
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Данные профиля обновлены.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Загрузка или обновление аватара.
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user();

        if (! $user->consent_pd) {
            return response()->json(['message' => 'Необходимо согласие на обработку персональных данных.'], 403);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        // Удаляем старый файл
        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

        // Встроенная обработка Laravel 13
        $path = $request->image('avatar')
            ->orient()             // Автоповорот по EXIF (важно для фото с телефона)
            ->cover(400, 400)      // Обрезаем до квадрата 400x400
            ->toWebp()             // Конвертируем в WebP
            ->quality(85)          // Устанавливаем качество
            ->store('avatars', 'yandex'); // Сохраняем на ваш диск

        if (! $path) {
            return response()->json(['message' => 'Не удалось сохранить файл.'], 500);
        }

        $user->update(['avatar_path' => $path]);

        return response()->json([
            'message' => 'Аватар обновлён.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Удаление аватара.
     */
    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return response()->json([
            'message' => 'Аватар удалён.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Отзыв согласия на обработку ПДн.
     * По 152-ФЗ пользователь имеет право отозвать согласие.
     */
    public function revokeConsent(Request $request)
    {
        $user = $request->user();

        // Удаляем аватар
        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

        // Очищаем дополнительные данные и отзываем согласие
        $user->update([
            'last_name' => null,
            'middle_name' => null,
            'phone' => null,
            'city' => null,
            'church_name' => null,
            'birth_date' => null,
            'about' => null,
            'marital_status' => null,
            'gender' => null,
            'avatar_path' => null,
            'consent_pd' => false,
            'consent_pd_at' => null,
            'consent_pd_ip' => null,
        ]);

        return response()->json([
            'message' => 'Согласие отозвано, дополнительные данные удалены.',
            'user' => $user->fresh(),
        ]);
    }
}
