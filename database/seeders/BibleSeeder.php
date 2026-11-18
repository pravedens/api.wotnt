<?php

namespace Database\Seeders;

use App\Models\BibleVerse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BibleSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/RU_SYNODAL.json');

        if (!File::exists($path)) {
            $this->command->error("Файл не найден: {$path}");
            return;
        }

        $content = File::get($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Ошибка JSON: ' . json_last_error_msg());
            return;
        }

        // Точный маппинг названий из файла scrollmapper на канонические ID
                $englishToId = [
            'Genesis' => 1, 'Exodus' => 2, 'Leviticus' => 3, 'Numbers' => 4, 'Deuteronomy' => 5,
            'Joshua' => 6, 'Judges' => 7, 'Ruth' => 8,
            'I Samuel' => 9, 'II Samuel' => 10,
            'I Kings' => 11, 'II Kings' => 12,
            'I Chronicles' => 13, 'II Chronicles' => 14,
            'Ezra' => 15, 'Nehemiah' => 16, 'Esther' => 17, 'Job' => 18, 'Psalms' => 19, 'Proverbs' => 20,
            'Ecclesiastes' => 21, 'Song of Solomon' => 22, 'Isaiah' => 23, 'Jeremiah' => 24, 'Lamentations' => 25,
            'Ezekiel' => 26, 'Daniel' => 27, 'Hosea' => 28, 'Joel' => 29, 'Amos' => 30,
            'Obadiah' => 31, 'Jonah' => 32, 'Micah' => 33, 'Nahum' => 34, 'Habakkuk' => 35,
            'Zephaniah' => 36, 'Haggai' => 37, 'Zechariah' => 38, 'Malachi' => 39,
            'Matthew' => 40, 'Mark' => 41, 'Luke' => 42, 'John' => 43, 'Acts' => 44,
            'James' => 45, 'I Peter' => 46, 'II Peter' => 47, 'I John' => 48, 'II John' => 49,
            'III John' => 50, 'Jude' => 51, 'Romans' => 52, 'I Corinthians' => 53, 'II Corinthians' => 54,
            'Galatians' => 55, 'Ephesians' => 56, 'Philippians' => 57, 'Colossians' => 58,
            'I Thessalonians' => 59, 'II Thessalonians' => 60,
            'I Timothy' => 61, 'II Timothy' => 62,
            'Titus' => 63, 'Philemon' => 64, 'Hebrews' => 65,
            'Revelation of John' => 66,
        ];

        // Русские названия для админки
        $russianNames = [
            1 => 'Бытие', 2 => 'Исход', 3 => 'Левит', 4 => 'Числа', 5 => 'Второзаконие',
            6 => 'Иисус Навин', 7 => 'Судьи', 8 => 'Руфь', 9 => '1 Царств', 10 => '2 Царств',
            11 => '3 Царств', 12 => '4 Царств', 13 => '1 Паралипоменон', 14 => '2 Паралипоменон',
            15 => 'Ездра', 16 => 'Неемия', 17 => 'Есфирь', 18 => 'Иов', 19 => 'Псалтирь',
            20 => 'Притчи', 21 => 'Екклесиаст', 22 => 'Песнь Песней', 23 => 'Исаия',
            24 => 'Иеремия', 25 => 'Плач Иеремии', 26 => 'Иезекииль', 27 => 'Даниил',
            28 => 'Осия', 29 => 'Иоиль', 30 => 'Амос', 31 => 'Авдий', 32 => 'Иона',
            33 => 'Михей', 34 => 'Наум', 35 => 'Аввакум', 36 => 'Софония', 37 => 'Аггей',
            38 => 'Захария', 39 => 'Малахия', 40 => 'От Матфея', 41 => 'От Марка',
            42 => 'От Луки', 43 => 'От Иоанна', 44 => 'Деяния', 45 => 'Иакова',
            46 => '1 Петра', 47 => '2 Петра', 48 => '1 Иоанна', 49 => '2 Иоанна',
            50 => '3 Иоанна', 51 => 'Иуды', 52 => 'К Римлянам', 53 => '1 Коринфянам',
            54 => '2 Коринфянам', 55 => 'К Галатам', 56 => 'К Ефесянам',
            57 => 'К Филиппийцам', 58 => 'К Колоссянам', 59 => '1 Фессалоникийцам',
            60 => '2 Фессалоникийцам', 61 => '1 Тимофею', 62 => '2 Тимофею',
            63 => 'К Титу', 64 => 'К Филимону', 65 => 'К Евреям', 66 => 'Откровение',
        ];

        $now = now();
        $rows = [];
        $unknownBooks = [];

        foreach ($data['books'] as $book) {
            $englishName = $book['name'] ?? '';

            // Ищем правильный ID по английскому названию
            $bookId = $englishToId[$englishName] ?? null;

            if (!$bookId) {
                $unknownBooks[] = $englishName;
                continue; // Пропускаем неизвестные книги
            }

            $bookName = $russianNames[$bookId] ?? $englishName;

            foreach ($book['chapters'] as $chapter) {
                $chapterId = $chapter['chapter'] ?? 0;

                foreach ($chapter['verses'] as $verse) {
                    $rows[] = [
                        'book_id'    => $bookId,
                        'book_name'  => $bookName,
                        'chapter_id' => $chapterId,
                        'verse_id'   => $verse['verse'] ?? 0,
                        'text'       => trim($verse['text'] ?? ''),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($unknownBooks)) {
            $this->command->warn('Не найдены в маппинге (пропущены): ' . implode(', ', array_unique($unknownBooks)));
        }

        // Чистим таблицу и загружаем
        BibleVerse::truncate();

        foreach (array_chunk($rows, 1000) as $chunk) {
            BibleVerse::insert($chunk);
        }

        $this->command->info('Импортировано стихов: ' . count($rows));
    }
}
