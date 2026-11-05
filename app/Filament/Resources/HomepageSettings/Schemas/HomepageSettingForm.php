<?php

namespace App\Filament\Resources\HomepageSettings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HomepageSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Содержимое блока')
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок блока')
                            ->required(),
                        TextInput::make('greeting_text')
                            ->label('Текст поздравления / поддержки')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Стилизация (Цвета и шрифт)')
                    ->schema([
                        ColorPicker::make('bg_color')
                            ->label('Цвет фона блока (HEX)')
                            ->required(),
                        ColorPicker::make('text_color')
                            ->label('Цвет текста (HEX)')
                            ->required(),
                        TextInput::make('font_family')
                            ->label('Шрифт (CSS)')
                            ->helperText('Например: sans-serif, serif, Montserrat')
                            ->default('sans-serif')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
