<?php

namespace App\Filament\Resources\PostComments\Pages;

use App\Filament\Resources\PostComments\PostCommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostComments extends ListRecords
{
    protected static string $resource = PostCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
