<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conference_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('group_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('thumbnail')
                    ->searchable(),
                TextColumn::make('youtube')
                    ->searchable(),
                TextColumn::make('rutube')
                    ->searchable(),
                TextColumn::make('dzen')
                    ->searchable(),
                TextColumn::make('vkVideo')
                    ->searchable(),
                TextColumn::make('audio_file')
                    ->searchable(),
                TextColumn::make('audio_filename')
                    ->searchable(),
                TextColumn::make('audio_size')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('audio_mime')
                    ->searchable(),
                TextColumn::make('text')
                    ->searchable(),
                TextColumn::make('text_file')
                    ->searchable(),
                TextColumn::make('text_filename')
                    ->searchable(),
                TextColumn::make('text_size')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('text_mime')
                    ->searchable(),
                TextColumn::make('views_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('likes_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
