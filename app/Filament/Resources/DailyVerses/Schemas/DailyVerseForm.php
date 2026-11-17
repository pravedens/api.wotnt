<?php

namespace App\Filament\Resources\DailyVerses\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Forms\Components\BibleVerseSelector;

class DailyVerseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ...BibleVerseSelector::make(prefix: 'bible', withRange: true),
            ]);
    }
}
