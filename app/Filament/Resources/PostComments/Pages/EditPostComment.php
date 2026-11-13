<?php

namespace App\Filament\Resources\PostComments\Pages;

use App\Filament\Resources\PostComments\PostCommentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostComment extends EditRecord
{
    protected static string $resource = PostCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
