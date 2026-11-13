<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'content' => $this->content,
            'likes_count' => $this->likes_count,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at?->toIso8601String(),

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user->avatar_url,
            ],

            // ✅ Вложенные ответы (если загружены)
            'replies' => PostCommentResource::collection(
                $this->whenLoaded('replies')
            ),

            // ✅ Лайкнут ли комментарий текущим пользователем
            'is_liked' => $this->when(
                $user !== null,
                fn () => $this->likes()->where('user_id', $user->id)->exists()
            ),

            // ✅ Может ли текущий пользователь редактировать/удалить
            'can_edit' => $this->when(
                $user !== null,
                fn () => $user->id === $this->user_id && (bool) $user->consent_pd
            ),
        ];
    }
}
