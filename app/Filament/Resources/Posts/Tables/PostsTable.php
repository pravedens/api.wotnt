<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Превью')
                    ->square()
                    ->checkFileExistence(false),
                TextColumn::make('title')
                    ->label('Название проповеди')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.title')
                    ->label('Спикер')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('conference.title')
                    ->label('Мероприятие')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('group.title')
                    ->label('Год')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('author.name')
                    ->label('Автор')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('views_count')
                    ->label('Просмотры')
                    ->searchable()
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('likes_count')
                    ->label('Лайки')
                    ->searchable()
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
