<?php

namespace App\Filament\Resources\HomepageSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Название блока')
                    ->searchable(),
                ColorColumn::make('bg_color')
                    ->label('Фон'),
                ColorColumn::make('text_color')
                    ->label('Текст'),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime(),
            ])
            ->actions([
                EditAction::make()->label('Редактировать'),
            ]);
    }
}
