<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Laravel\Passkeys\Passkey;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Роли')
                    ->badge()
                    ->listWithLineBreaks(),
                IconColumn::make('email_verified_at')
                    ->label('Почта')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null),
                IconColumn::make('two_factor_secret')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->two_factor_secret !== null),
                TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),

                // ✅ Действие: Быстрое управление ролями из таблицы
                Action::make('roles')
                    ->label('Роли')
                    ->icon('heroicon-o-shield-check')
                    ->form([
                        CheckboxList::make('roles')
                            ->label('Выберите роли')
                            ->options(fn () => Role::pluck('name', 'name')->toArray())
                            ->columns(2),
                    ])
                    ->fillForm(fn (User $record): array => [
                        'roles' => $record->roles->pluck('name')->toArray(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->syncRoles($data['roles'] ?? []);
                    }),

                // ✅ Действие: Сброс 2FA
                Action::make('reset2fa')
                    ->label('Сброс 2FA')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                        ])->save();
                    }),

                // ✅ Действие: Сброс Passkeys
                Action::make('resetPasskeys')
                    ->label('Сброс Passkeys')
                    ->icon('heroicon-o-finger-print')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        Passkey::where('user_id', $record->id)->delete();
                    }),
            ]);
    }
}
