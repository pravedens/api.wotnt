<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Изображение')
                    ->schema([
                        FileUpload::make('thumbnail')
                            ->label('Аватар / превью спикера')
                            ->disk('yandex')
                            ->directory('categories/thumbnails')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->helperText('Квадратное изображение, рекомендуется 600×600. Формат: WebP.')
                            ->columnSpanFull()
                            ->saveUploadedFileUsing(function ($file) {
                                return Image::fromUpload($file)
                                    ->orient()
                                    ->cover(600, 600)   // 1:1
                                    ->toWebp()
                                    ->quality(85)
                                    ->store(path: 'categories/thumbnails', disk: 'yandex');
                            }),
                    ]),
            ]);
    }
}
