<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;

class VideoSelector
{
    public static function make(string $prefix = 'video'): array
    {
        $p = fn (string $key) => $prefix . '_' . $key;

        return [
            // 1. Выбор платформы
            Select::make($p('platform'))
                ->label('Видеоплатформа')
                ->options([
                    'rutube' => 'Rutube',
                    'vk' => 'VK Видео',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) use ($p) {
                    $set($p('url'), null); // Очищаем URL при смене платформы
                })
                // При редактировании заполняем поле на основе того, что уже есть в базе
                ->afterStateHydrated(function (Select $component, Get $get, ?string $state) {
                    if ($state) return; // Если уже выбрано, не трогаем
                    if ($get('rutube')) {
                        $component->state('rutube');
                    } elseif ($get('vkVideo')) {
                        $component->state('vk');
                    }
                }),

            // 2. URL или ID видео
            TextInput::make($p('url'))
                ->label(fn (Get $get) => $get($p('platform')) === 'rutube'
                    ? 'ID видео Rutube (например, 1234567890abcdef)'
                    : 'Ссылка на VK Видео (полная)')
                ->required()
                ->helperText(fn (Get $get) => $get($p('platform')) === 'rutube'
                    ? 'Вставьте ID из ссылки: rutube.ru/video/1234567890abcdef/'
                    : 'Например: https://vk.com/video-123456_7891011')
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, $state) use ($p) {
                    // Сохраняем в реальные поля БД в зависимости от выбранной платформы
                    $platform = $get($p('platform'));
                    if ($platform === 'rutube') {
                        $set('rutube', $state);
                    } elseif ($platform === 'vk') {
                        $set('vkVideo', $state);
                    }
                })
                // При редактировании подтягиваем данные из базы
                ->afterStateHydrated(function (TextInput $component, Get $get) use ($p) {
                    $platform = $get($p('platform'));
                    if ($platform === 'rutube' && $get('rutube')) {
                        $component->state($get('rutube'));
                    } elseif ($platform === 'vk' && $get('vkVideo')) {
                        $component->state($get('vkVideo'));
                    }
                }),

            // 3. Переключатель доступа (публичное/приватное)
            Toggle::make($p('public'))
                ->label('Публичное видео (видят все)')
                ->default(true)
                ->live(), // Обязательно live() для реакции чекбоксов

            // 4. Чекбоксы ролей (видны только если НЕ публичное)
            CheckboxList::make($p('allowed_roles'))
                ->label('Разрешить просмотр ролям')
                ->options([
                    'minister' => 'Служитель (minister)',
                    'pastor' => 'Пастор (pastor)',
                    'user' => 'Пользователь (user)',
                    'member' => 'Член церкви (member)',
                    'pray' => 'Молитвенник (pray)',
                    'teacher' => 'Учитель (teacher)',
                    'student' => 'Ученик (student)',
                ])
                ->visible(fn (Get $get) => ! $get($p('public'))) // Скрываем, если публичное
                ->required(fn (Get $get) => ! $get($p('public'))),

            // 5. Чекбоксы пола (видны только если НЕ публичное)
            CheckboxList::make($p('allowed_genders'))
                ->label('Разрешить просмотр полу')
                ->options([
                    'male' => 'Мужчинам',
                    'female' => 'Женщинам',
                ])
                ->visible(fn (Get $get) => ! $get($p('public')))
                ->required(fn (Get $get) => ! $get($p('public'))),
        ];
    }
}
