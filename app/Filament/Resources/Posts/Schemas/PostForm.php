<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('PostTabs')
                    ->tabs([
                        // ─────────────── ВКЛАДКА 1: Основное ───────────────
                        Tab::make('Основное')
                            ->schema([
                                Section::make('Заголовок и описание')
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
                                            ->label('Краткое описание')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->label('Полный текст проповеди')
                                            ->required()
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold', 'italic', 'underline', 'strike',
                                                'h2', 'h3',
                                                'bulletList', 'orderedList', 'blockquote',
                                                'link', 'undo', 'redo',
                                            ]),
                                    ]),

                                Section::make('Классификация')
                                    ->columns(3)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Спикер')
                                            ->relationship('category', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('group_id')
                                            ->label('Год')
                                            ->relationship('group', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('conference_id')
                                            ->label('Мероприятие')
                                            ->relationship('conference', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('user_id')
                                            ->label('Автор')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(fn () => Filament::auth()->id())
                                            ->required(),
                                    ]),

                                Section::make('Обложка')
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Превью')
                                            ->disk('yandex')
                                            ->directory('posts/thumbnails')
                                            ->image()
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->helperText('Рекомендуемый размер: 1280×720. Формат: WebP.')
                                            ->columnSpanFull()
                                            ->saveUploadedFileUsing(function ($file) {
                                                return Image::fromUpload($file)
                                                    ->orient()
                                                    ->cover(1280, 720)   // обрезка под 16:9
                                                    ->toWebp()
                                                    ->quality(85)
                                                    ->store(path: 'posts/thumbnails', disk: 'yandex');
                                            }),
                                    ]),
                            ]),

                        // ─────────────── ВКЛАДКА 2: Видео ───────────────
                        Tab::make('Видео')
                            ->schema([
                                Section::make('Ссылки на видеоплатформы')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('rutube')
                                            ->label('Rutube (URL)')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('youtube')
                                            ->label('YouTube (ID или URL)')
                                            ->maxLength(255),

                                        TextInput::make('dzen')
                                            ->label('Дзен (URL)')
                                            ->url()
                                            ->maxLength(255),

                                        TextInput::make('vkVideo')
                                            ->label('VK Видео (URL)')
                                            ->url()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // ─────────────── ВКЛАДКА 3: Аудио и текст ───────────────
                        Tab::make('Аудио и текст')
                            ->schema([
                                Section::make('Аудио проповеди')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('audio_file')
                                            ->label('Аудиофайл')
                                            ->disk('yandex')
                                            ->directory('posts/audio')
                                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'])
                                            ->maxSize(102400) // 100 MB
                                            ->preserveFilenames()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $file = is_array($state) ? array_values($state)[0] : $state;
                                                    if ($file && ! is_string($file)) {
                                                        $set('audio_filename', $file->getClientOriginalName());
                                                        $set('audio_size', $file->getSize());
                                                        $set('audio_mime', $file->getMimeType());
                                                    }
                                                }
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('audio_filename')
                                            ->label('Имя файла')
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('audio_size')
                                            ->label('Размер (байт)')
                                            ->disabled()
                                            ->dehydrated()
                                            ->numeric(),

                                        TextInput::make('audio_mime')
                                            ->label('MIME-тип')
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('audio')
                                            ->label('Старая ссылка на аудио (legacy)')
                                            ->helperText('Оставьте пустым, если используете FileUpload.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Текст проповеди (файл)')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('text_file')
                                            ->label('Файл с текстом')
                                            ->disk('yandex')
                                            ->directory('posts/texts')
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                                'application/msword',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                'text/plain',
                                            ])
                                            ->maxSize(20480) // 20 MB
                                            ->preserveFilenames()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $file = is_array($state) ? array_values($state)[0] : $state;
                                                    if ($file && ! is_string($file)) {
                                                        $set('text_filename', $file->getClientOriginalName());
                                                        $set('text_size', $file->getSize());
                                                        $set('text_mime', $file->getMimeType());
                                                    }
                                                }
                                            })
                                            ->columnSpanFull(),

                                        TextInput::make('text_filename')
                                            ->label('Имя файла')
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('text_size')
                                            ->label('Размер (байт)')
                                            ->disabled()
                                            ->dehydrated()
                                            ->numeric(),

                                        TextInput::make('text_mime')
                                            ->label('MIME-тип')
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('text')
                                            ->label('Legacy-поле text')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ─────────────── ВКЛАДКА 4: Статистика ───────────────
                        Tab::make('Статистика')
                            ->schema([
                                Section::make('Счётчики')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('views_count')
                                            ->label('Просмотры')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('likes_count')
                                            ->label('Лайки')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
