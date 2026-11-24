<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }

    public function viewVideo(?User $user, Post $post): Response
    {
        // Если видео публичное — пускаем всех
        if ($post->video_public) {
            return Response::allow();
        }

        // Если пользователь не авторизован — запрещаем
        if (! $user) {
            return Response::deny('Видео доступно только авторизованным пользователям.');
        }

        // Проверка роли
        $allowedRoles = $post->video_allowed_roles ?? [];
        if (! empty($allowedRoles) && ! in_array($user->role, $allowedRoles, true)) {
            return Response::deny('У вас нет роли, которая может смотреть это видео.');
        }

        // Проверка пола
        $allowedGenders = $post->video_allowed_genders ?? [];
        if (! empty($allowedGenders) && ! in_array($user->gender, $allowedGenders, true)) {
            return Response::deny('Просмотр этого видео разрешён только определённому полу.');
        }

        return Response::allow();
    }
}
