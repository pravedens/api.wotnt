<?php

namespace App\Filament\Resources\HomepageSettings;

use App\Filament\Resources\HomepageSettings\Pages\CreateHomepageSetting;
use App\Filament\Resources\HomepageSettings\Pages\EditHomepageSetting;
use App\Filament\Resources\HomepageSettings\Pages\ListHomepageSettings;
use App\Filament\Resources\HomepageSettings\Schemas\HomepageSettingForm;
use App\Filament\Resources\HomepageSettings\Tables\HomepageSettingsTable;
use App\Models\HomepageSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HomepageSettingResource extends Resource
{
    protected static ?string $navigationLabel = 'Главная страница';

    protected static ?string $modelLabel = 'Настройка блока';

    protected static ?string $pluralModelLabel = 'Настройки главной';

    protected static ?string $model = HomepageSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return HomepageSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HomepageSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Запрещаем создавать новые
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Запрещаем удалять
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomepageSettings::route('/'),
            'create' => CreateHomepageSetting::route('/create'),
            'edit' => EditHomepageSetting::route('/{record}/edit'),
        ];
    }
}
