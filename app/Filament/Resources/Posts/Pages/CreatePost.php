<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();

        // Страховка: переносим URL в нужное поле на основе выбранной платформы
        if (! empty($data['video_platform']) && ! empty($data['video_url'])) {
            if ($data['video_platform'] === 'rutube') {
                $data['rutube'] = $data['video_url'];
            } elseif ($data['video_platform'] === 'vk') {
                $data['vkVideo'] = $data['video_url'];
            }
        }

        return $data;
    }
}
