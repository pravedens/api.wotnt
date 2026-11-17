<?php

namespace App\Filament\Resources\DailyVerses\Pages;

use App\Filament\Resources\DailyVerses\DailyVerseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyVerses extends ListRecords
{
    protected static string $resource = DailyVerseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
