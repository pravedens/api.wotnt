<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyVerse extends Model
{
    protected $fillable = [
    'book_id', 'chapter_id', 'verse_id', 'verse_to_id',
    'reference', 'text',
];
}
