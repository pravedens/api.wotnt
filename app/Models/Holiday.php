<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Holiday extends Model
{
    protected $fillable = [
        'title',
        'date',
        'end_date',
        'is_annual',
        'description',
        'image_path',
        'text_color',
        'bg_color',
        'font_family',
        'title_font_family',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
        'is_annual' => 'boolean',
    ];

    // Аксессор для картинки (S3 Yandex)
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image_path) {
                    return null;
                }

                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('yandex');

                return $disk->url($this->image_path);
            }
        );
    }
}
