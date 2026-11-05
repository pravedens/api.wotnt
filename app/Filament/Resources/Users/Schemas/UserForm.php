<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('last_name')
                            ->label('Фамилия'),
                        TextInput::make('middle_name')
                            ->label('Отчество'),
                        TextInput::make('phone')
                            ->label('Телефон'),
                        TextInput::make('city')
                            ->label('Город'),
                        TextInput::make('church_name')
                            ->label('Церковь'),
                        DatePicker::make('birth_date')
                            ->label('Дата рождения'),
                        Select::make('gender')
                            ->label('Пол')
                            ->options([
                                'male' => 'Мужской',
                                'female' => 'Женский',
                            ]),
                        Select::make('marital_status')
                            ->label('Семейное положение')
                            ->options([
                                'single' => 'Не женат / Не замужем',
                                'married' => 'Женат / Замужем',
                                'divorced' => 'Разведён(а)',
                                'widowed' => 'Вдовец / Вдова',
                            ]),
                        Textarea::make('about')
                            ->label('О себе')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Права доступа и согласия')
                    ->schema([
                        // ✅ Добавляем управление ролями Spatie
                        Select::make('roles')
                            ->label('Роли')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Управление ролями пользователя (Spatie)'),

                        Toggle::make('consent_pd')
                            ->label('Согласие на обработку ПДн')
                            ->helperText('Если выключить, пользователь потеряет доступ к расширенному профилю, а роли и ключи безопасности будут сброшены.'),

                        Toggle::make('is_deceased')
                            ->label('Ушел из жизни')
                            ->helperText('Пользователь будет отображаться в мемориальном разделе на главной странице.'),
                        DatePicker::make('death_date')->label('Дата смерти')->visible(fn ($get) => $get('is_deceased')),
                        DatePicker::make('funeral_date')->label('Дата похорон')->visible(fn ($get) => $get('is_deceased')),
                    ])->columns(2),
            ]);
    }
}
