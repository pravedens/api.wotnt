<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Информация о празднике')
                    ->schema([
                        TextInput::make('title')
                            ->label('Название праздника')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->label('Дата')
                            ->required(),
                        Textarea::make('description')
                            ->label('Текст поздравления')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Изображение')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Картинка')
                            ->image()
                            ->disk('yandex') // ✅ Сохраняем в Yandex Cloud
                            ->directory('holidays') // Папка в S3
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
