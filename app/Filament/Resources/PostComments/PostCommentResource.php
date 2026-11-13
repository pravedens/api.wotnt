<?php

namespace App\Filament\Resources\PostComments;

use App\Filament\Resources\PostComments\Pages\CreatePostComment;
use App\Filament\Resources\PostComments\Pages\EditPostComment;
use App\Filament\Resources\PostComments\Pages\ListPostComments;
use App\Filament\Resources\PostComments\Schemas\PostCommentForm;
use App\Filament\Resources\PostComments\Tables\PostCommentsTable;
use App\Models\PostComment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostCommentResource extends Resource
{
    protected static ?string $model = PostComment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PostCommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostCommentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostComments::route('/'),
            'create' => CreatePostComment::route('/create'),
            'edit' => EditPostComment::route('/{record}/edit'),
        ];
    }
}
