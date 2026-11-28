<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Та же страховка для редактирования
        if (! empty($data['video_platform']) && ! empty($data['video_url'])) {
            if ($data['video_platform'] === 'rutube') {
                $data['rutube'] = $data['video_url'];
            } elseif ($data['video_platform'] === 'vk') {
                $data['vkVideo'] = $data['video_url'];
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
