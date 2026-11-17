<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleVerse extends Model
{
    protected $fillable = [
        'book_id',
        'book_name',
        'chapter_id',
        'verse_id',
        'text',
    ];

    protected $casts = [
        'book_id'    => 'integer',
        'chapter_id' => 'integer',
        'verse_id'   => 'integer',
    ];
}
