<?php

namespace App\Services;

use App\Models\DailyVerse;

class DailyVerseService
{
    /**
     * Возвращает случайный стих из таблицы daily_verses.
     * Каждый запрос — новый стих.
     */
    public function getRandom(): ?array
    {
        $total = DailyVerse::count();
        if ($total === 0) {
            return null;
        }

        $seed = crc32(now()->toDateString());
        $verse = DailyVerse::skip($seed % $total)->first();

        return [
            'reference' => $verse->reference,
            'text' => $verse->text,
        ];
    }
}
