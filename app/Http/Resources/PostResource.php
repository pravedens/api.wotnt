<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $canAccessMedia = $user && $user->consent_pd;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,

            // Медиа (относительные пути — для отладки)
            'thumbnail' => $this->thumbnail,
            // ✅ Готовые URL через аксессоры
            'thumbnail_url' => $this->thumbnail_url,

            // Видео
            'youtube' => $this->youtube,
            'rutube' => $this->rutube,
            'dzen' => $this->dzen,
            'vkVideo' => $this->vkVideo,

            // Аудио (только при согласии)
            'audio_file' => $this->audio_file,
            'audio_filename' => $this->audio_filename,
            'audio_size' => $this->audio_size,
            'audio_mime' => $this->audio_mime,
            'audio_url' => $canAccessMedia ? $this->audio_url : null,

            // Текст (только при согласии)
            'text_file' => $this->text_file,
            'text_filename' => $this->text_filename,
            'text_size' => $this->text_size,
            'text_mime' => $this->text_mime,
            'text_file_url' => $canAccessMedia ? $this->text_file_url : null,

            // Флаг для фронта: нужны ли права на медиа
            'requires_consent' => ! $canAccessMedia && ($this->audio_file || $this->text_file),

            // Связи
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'conference' => ConferenceResource::make($this->whenLoaded('conference')),
            'group' => GroupResource::make($this->whenLoaded('group')),
            'author' => UserResource::make($this->whenLoaded('author')),

            // Комментарии (если загружены)
            'comments' => PostCommentResource::collection($this->whenLoaded('comments')),

            // Персонализация
            'is_liked' => $this->when(isset($this->is_liked), $this->is_liked),
            'is_favorited' => $this->when(isset($this->is_favorited), $this->is_favorited),
        ];
    }
}
