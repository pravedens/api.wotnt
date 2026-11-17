<?php

namespace Database\Seeders;

use App\Models\BibleVerse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BibleSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/bible.json');

        if (!File::exists($path)) {
            $this->command->error("Файл не найден: {$path}");
            return;
        }

        $content = File::get($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // убираем BOM
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Ошибка JSON: ' . json_last_error_msg());
            return;
        }

        if (!isset($data['Books'])) {
            $this->command->error('В JSON нет ключа "Books".');
            return;
        }

        $now = now();
        $rows = [];

        foreach ($data['Books'] as $book) {
            $bookId   = $book['BookId']   ?? 0;
            $bookName = $book['BookName'] ?? 'Неизвестно';

            foreach ($book['Chapters'] as $chapter) {
                $chapterId = $chapter['ChapterId'] ?? 0;

                foreach ($chapter['Verses'] as $verse) {
                    $rows[] = [
                        'book_id'    => $bookId,
                        'book_name'  => $bookName,
                        'chapter_id' => $chapterId,
                        'verse_id'   => $verse['VerseId'] ?? 0,
                        'text'       => trim($verse['Text'] ?? ''),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Чистим таблицу перед импортом (на случай повторного запуска)
        BibleVerse::truncate();

        foreach (array_chunk($rows, 1000) as $chunk) {
            BibleVerse::insert($chunk);
        }

        $this->command->info('Импортировано стихов: ' . count($rows));
    }
}
