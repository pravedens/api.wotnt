<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Holiday extends Model
{
    protected $fillable = [
        'title',
        'date',
        'description',
        'image_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Аксессор для картинки (S3 Yandex)
    protected function imageUrl(): Attribute
{
    return Attribute::make(
        get: function () {
            if (! $this->image_path) {
                return null;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = \Illuminate\Support\Facades\Storage::disk('yandex');

            return $disk->url($this->image_path);
        }
    );
}
}
