<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                        Toggle::make('is_annual')
                            ->label('Повторять ежегодно')
                            ->default(true)
                            ->helperText('Если включено, год в дате будет игнорироваться.'),

                        DatePicker::make('date')
                            ->label('Дата начала (или основная дата)')
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Дата окончания (если праздник длится несколько дней)')
                            ->helperText('Оставьте пустым, если праздник однодневный.'),
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

                Section::make('Стилизация')
                    ->schema([
                        ColorPicker::make('text_color')->label('Цвет текста'),
                        ColorPicker::make('bg_color')->label('Цвет фона'),
                        TextInput::make('font_family')->label('Шрифт текста (CSS)')->default('sans-serif'),
                        TextInput::make('title_font_family')->label('Шрифт заголовка (CSS)')->default('sans-serif'),
                    ])->columns(2),
            ]);
    }
}
