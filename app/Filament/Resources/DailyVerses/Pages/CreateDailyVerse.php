<?php

namespace App\Filament\Resources\DailyVerses\Pages;

use App\Filament\Resources\DailyVerses\DailyVerseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyVerse extends CreateRecord
{
    protected static string $resource = DailyVerseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['book_id'] = $data['bible_book_id'] ?? null;
        $data['chapter_id'] = $data['bible_chapter_id'] ?? null;
        $data['verse_id'] = $data['bible_verse_id'] ?? null;
        $data['verse_to_id'] = $data['bible_verse_to_id'] ?? null;
        $data['reference'] = $data['bible_reference'] ?? null;
        $data['text'] = $data['bible_text'] ?? null;

        unset(
            $data['bible_book_id'], $data['bible_chapter_id'],
            $data['bible_verse_id'], $data['bible_verse_to_id'],
            $data['bible_is_range'], $data['bible_reference'],
            $data['bible_text'],
        );

        return $data;
    }
}
