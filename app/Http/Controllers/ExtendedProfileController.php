<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Http\Resources\UserResource;

class ExtendedProfileController extends Controller
{
    /**
     * Обновление текстовых данных профиля.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $consent = $request->boolean('consent_pd');

        // ⚠️ 1. Если пользователь СНИМАЕТ галочку (было true, стало false)
        if (!$consent && $user->consent_pd) {
            return $this->revokeConsentLogic($user);
        }

        // Если согласие так и не было дано, но пользователь пытается сохранить данные без галочки
        if (!$consent && !$user->consent_pd) {
            return response()->json([
                'message' => 'Необходимо согласие на обработку персональных данных.',
                'errors' => ['consent_pd' => ['Согласие обязательно.']]
            ], 422);
        }

        // ⚠️ 2. Если согласие дано (или уже было), валидируем и сохраняем данные
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
        if (!$user->consent_pd) {
            $validated['consent_pd'] = true;
            $validated['consent_pd_at'] = now();
            $validated['consent_pd_ip'] = $request->ip();
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Данные профиля обновлены.',
            'user' => UserResource::make($user->fresh())->resolve(),
        ]);
    }

    /**
     * Логика отзыва согласия (очистка данных).
     * Вызывается из update, когда пользователь снимает галочку.
     */
    private function revokeConsentLogic(User $user)
    {
        // Удаляем аватар из S3
        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

        // Очищаем все дополнительные поля
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
            'message' => 'Согласие отозвано, дополнительные данные и фото удалены.',
            'user' => UserResource::make($user->fresh())->resolve(),
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
            'user' => UserResource::make($user->fresh())->resolve(),
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
            'user' => UserResource::make($user->fresh())->resolve(),
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
            'user' => UserResource::make($user->fresh())->resolve(),
        ]);
    }
}
