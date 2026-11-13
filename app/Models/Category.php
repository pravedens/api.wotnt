<?php

namespace App\Models;

use App\Models\Concerns\HasStorageUrls;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'description', 'thumbnail'])]
class Category extends Model
{
    use HasFactory, HasStorageUrls;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->storageUrl($this->thumbnail),
        );
    }
}
