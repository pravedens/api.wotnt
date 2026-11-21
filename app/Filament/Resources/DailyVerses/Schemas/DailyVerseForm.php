<?php

namespace App\Filament\Resources\DailyVerses\Schemas;

use App\Filament\Forms\Components\BibleVerseSelector;
use App\Models\DailyVerse;
use Filament\Schemas\Schema;

class DailyVerseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ...BibleVerseSelector::make(
                    prefix: 'bible',
                    withRange: true,
                    uniqueTable: DailyVerse::class,
                    uniqueColumn: 'reference'
                ),
            ]);
    }
}
