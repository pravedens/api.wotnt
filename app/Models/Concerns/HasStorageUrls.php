<?php

namespace App\Models\Concerns;

use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;

trait HasStorageUrls
{
    protected function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        /** @var AwsS3V3Adapter $disk */
        $disk = Storage::disk('yandex');

        return $disk->url($path);
    }
}
