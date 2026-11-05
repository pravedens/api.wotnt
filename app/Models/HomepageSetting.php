<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = [
        'key',
        'title',
        'greeting_text',
        'text_color',
        'bg_color',
        'font_family',
    ];

    /**
     * Хелпер для получения настроек по ключу
     */
    public static function get(string $key)
    {
        return self::where('key', $key)->first();
    }
}
