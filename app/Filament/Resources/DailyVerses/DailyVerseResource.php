<?php

namespace App\Filament\Resources\DailyVerses;

use App\Filament\Resources\DailyVerses\Pages\CreateDailyVerse;
use App\Filament\Resources\DailyVerses\Pages\EditDailyVerse;
use App\Filament\Resources\DailyVerses\Pages\ListDailyVerses;
use App\Filament\Resources\DailyVerses\Schemas\DailyVerseForm;
use App\Filament\Resources\DailyVerses\Tables\DailyVersesTable;
use App\Models\DailyVerse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyVerseResource extends Resource
{
    protected static ?string $model = DailyVerse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return DailyVerseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyVersesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyVerses::route('/'),
            'create' => CreateDailyVerse::route('/create'),
            'edit' => EditDailyVerse::route('/{record}/edit'),
        ];
    }
}
