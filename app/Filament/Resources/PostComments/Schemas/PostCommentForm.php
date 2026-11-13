<?php

namespace App\Filament\Resources\PostComments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostCommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('post_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('parent_id')
                    ->numeric(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->required(),
                TextInput::make('likes_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
