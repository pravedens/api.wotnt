<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExtendedProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExtendedProfileController extends Controller
{
    /**
     * Обновление текстовых данных профиля.
     */
    public function update(UpdateExtendedProfileRequest $request)
    {
        $user = $request->user();
        $consent = $request->boolean('consent_pd');

        // 1. Если пользователь СНИМАЕТ галочку (было true, стало false)
        if (! $consent && $user->consent_pd) {
            return $this->revokeConsentLogic($user);
        }

        // 2. Если согласие так и не было дано, но пользователь пытается сохранить данные без галочки
        if (! $consent && ! $user->consent_pd) {
            return response()->json([
                'message' => 'Необходимо согласие на обработку персональных данных.',
                'errors' => ['consent_pd' => ['Согласие обязательно.']],
            ], 422);
        }

        // 3. Валидированные данные (consent_pd уже проверен в Request)
        $validated = $request->validated();

        // Фиксируем согласие (если дано впервые)
        if (! $user->consent_pd) {
            $validated['consent_pd'] = true;
            $validated['consent_pd_at'] = now();
            $validated['consent_pd_ip'] = $request->ip();
        }

        $user->update($validated);

        // ✅ Логика назначения роли после согласия и подтверждения почты
        if ($user->consent_pd && $user->hasVerifiedEmail() && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        return response()->json([
            'message' => 'Данные профиля обновлены.',
            'user' => UserResource::make($user->fresh())->resolve(),
        ]);
    }

    /**
     * Логика отзыва согласия (очистка данных).
     */
    private function revokeConsentLogic(User $user)
    {
        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

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
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $user = $request->user();

        if (! $user->consent_pd) {
            return response()->json(['message' => 'Необходимо согласие на обработку персональных данных.'], 403);
        }

        // Удаляем старый файл
        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

        $path = $request->image('avatar')
            ->orient()
            ->cover(400, 400)
            ->toWebp()
            ->quality(85)
            ->store('avatars', 'yandex');

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
     */
    public function revokeConsent(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('yandex')->delete($user->avatar_path);
        }

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
