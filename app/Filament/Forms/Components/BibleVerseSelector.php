<?php

namespace App\Filament\Forms\Components;

use App\Models\BibleVerse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BibleVerseSelector
{
    /**
     * Возвращает массив полей для вставки в любую форму Filament.
     *
     * @param  string  $prefix  Префикс для имен полей (чтобы не конфликтовать с другими полями формы)
     * @param  bool  $withRange  Нужен ли выбор диапазона стихов
     */
    public static function make(
        string $prefix = 'bible',
        bool $withRange = true,
        ?string $uniqueTable = null,
        string $uniqueColumn = 'reference'
    ): array {
        $p = fn (string $key) => $prefix.'_'.$key;

        $fields = [
            Select::make($p('book_id'))
                ->label('Книга')
                ->options(config('bible.books')) // Берем чистый массив
                ->searchable()
                ->optionsLimit(100)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) use ($p) {
                    $set($p('chapter_id'), null);
                    $set($p('verse_id'), null);
                    $set($p('verse_to_id'), null);
                    $set($p('text'), null);
                    $set($p('reference'), null);
                }),

            Select::make($p('chapter_id'))
                ->label('Глава')
                ->options(function (Get $get) use ($p) {
                    $bookId = $get($p('book_id'));
                    if (! $bookId) {
                        return [];
                    }

                    return BibleVerse::query()
                        ->where('book_id', $bookId)
                        ->distinct()
                        ->orderBy('chapter_id')
                        ->pluck('chapter_id', 'chapter_id')
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set) use ($p) {
                    $set($p('verse_id'), null);
                    $set($p('verse_to_id'), null);
                    $set($p('text'), null);
                }),
        ];

        if ($withRange) {
            $fields[] = Toggle::make($p('is_range'))
                ->label('Диапазон стихов')
                ->live()
                ->afterStateUpdated(function (Set $set) use ($p) {
                    $set($p('verse_to_id'), null);
                    $set($p('text'), null);
                });
        }

        $fields[] = Select::make($p('verse_id'))
            ->label($withRange ? 'Стих (от)' : 'Стих')
            ->options(function (Get $get) use ($p) {
                $bookId = $get($p('book_id'));
                $chapterId = $get($p('chapter_id'));
                if (! $bookId || ! $chapterId) {
                    return [];
                }

                return BibleVerse::query()
                    ->where('book_id', $bookId)
                    ->where('chapter_id', $chapterId)
                    ->orderBy('verse_id')
                    ->pluck('verse_id', 'verse_id')
                    ->toArray();
            })
            ->searchable()
            ->required()
            ->live()
            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateText($get, $set, $prefix, $withRange));

        if ($withRange) {
            $fields[] = Select::make($p('verse_to_id'))
                ->label('Стих (до)')
                ->options(function (Get $get) use ($p) {
                    $bookId = $get($p('book_id'));
                    $chapterId = $get($p('chapter_id'));
                    $from = $get($p('verse_id'));
                    if (! $bookId || ! $chapterId || ! $from) {
                        return [];
                    }

                    return BibleVerse::query()
                        ->where('book_id', $bookId)
                        ->where('chapter_id', $chapterId)
                        ->where('verse_id', '>=', $from)
                        ->orderBy('verse_id')
                        ->pluck('verse_id', 'verse_id')
                        ->toArray();
                })
                ->searchable()
                ->live()
                ->visible(fn (Get $get) => (bool) $get($p('is_range')))
                ->required(fn (Get $get) => (bool) $get($p('is_range')))
                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateText($get, $set, $prefix, $withRange));
        }

        $referenceField = Textarea::make($p('reference'))
            ->label('Ссылка (авто)')
            ->disabled()
            ->dehydrated()
            ->rows(1)
            ->columnSpanFull();

        // Если передали таблицу для проверки — добавляем правило unique
        if ($uniqueTable) {
            $referenceField->unique(
                table: $uniqueTable,
                column: $uniqueColumn,
                ignoreRecord: true,
            )->validationMessages([
                'unique' => 'Такой стих уже добавлен в этот список.',
            ]);
        }

        $fields[] = $referenceField;

        $fields[] = Textarea::make($p('text'))
            ->label('Текст (авто)')
            ->disabled()
            ->dehydrated()
            ->rows(6)
            ->columnSpanFull();

        return $fields;
    }

    protected static function updateText(Get $get, Set $set, string $prefix, bool $withRange): void
    {
        $p = fn (string $key) => $prefix.'_'.$key;

        $bookId = $get($p('book_id'));
        $chapterId = $get($p('chapter_id'));
        $verseFrom = $get($p('verse_id'));
        $isRange = $withRange ? (bool) $get($p('is_range')) : false;
        $verseTo = $isRange ? $get($p('verse_to_id')) : $verseFrom;

        if (! $bookId || ! $chapterId || ! $verseFrom) {
            $set($p('text'), null);
            $set($p('reference'), null);

            return;
        }

        $query = BibleVerse::query()
            ->where('book_id', $bookId)
            ->where('chapter_id', $chapterId);

        if ($isRange && $verseTo) {
            $query->whereBetween('verse_id', [$verseFrom, $verseTo]);
        } else {
            $query->where('verse_id', $verseFrom);
        }

        $verses = $query->orderBy('verse_id')->get();

        if ($verses->isEmpty()) {
            $set($p('text'), 'Стих не найден');
            $set($p('reference'), null);

            return;
        }

        $bookName = $verses->first()->book_name;
        $rangeStr = ($isRange && $verseTo) ? "{$verseFrom}-{$verseTo}" : (string) $verseFrom;
        $reference = "{$bookName} {$chapterId}:{$rangeStr}";

        $text = $verses->map(fn ($v) => "{$v->verse_id}. {$v->text}")->implode("\n");

        $set($p('reference'), $reference);
        $set($p('text'), $text);
    }
}
