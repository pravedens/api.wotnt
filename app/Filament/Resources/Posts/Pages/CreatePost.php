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

        return $data;
    }
}
