<?php

namespace App\Filament\Resources\DailyVerses\Pages;

use App\Filament\Resources\DailyVerses\DailyVerseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyVerse extends EditRecord
{
    protected static string $resource = DailyVerseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Этот метод заполняет форму перед открытием страницы редактирования
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Подставляем сохранённые значения в поля компонента
        $data['bible_book_id']     = $data['book_id'] ?? null;
        $data['bible_chapter_id']  = $data['chapter_id'] ?? null;
        $data['bible_verse_id']    = $data['verse_id'] ?? null;
        $data['bible_verse_to_id'] = $data['verse_to_id'] ?? null;
        $data['bible_is_range']    = ! empty($data['verse_to_id']);
        $data['bible_reference']   = $data['reference'] ?? null;
        $data['bible_text']        = $data['text'] ?? null;

        return $data;
    }

    /**
     * Этот метод собирает данные перед сохранением
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['book_id']     = $data['bible_book_id'] ?? $data['book_id'] ?? null;
        $data['chapter_id']  = $data['bible_chapter_id'] ?? $data['chapter_id'] ?? null;
        $data['verse_id']    = $data['bible_verse_id'] ?? $data['verse_id'] ?? null;
        $data['verse_to_id'] = $data['bible_verse_to_id'] ?? $data['verse_to_id'] ?? null;
        $data['reference']   = $data['bible_reference'] ?? $data['reference'] ?? null;
        $data['text']        = $data['bible_text'] ?? $data['text'] ?? null;

        unset(
            $data['bible_book_id'],
            $data['bible_chapter_id'],
            $data['bible_verse_id'],
            $data['bible_verse_to_id'],
            $data['bible_is_range'],
            $data['bible_reference'],
            $data['bible_text'],
        );

        return $data;
    }
}
