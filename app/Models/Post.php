<?php

namespace App\Models;

use App\Models\Concerns\HasStorageUrls;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'slug', 'description', 'content',
    'category_id', 'conference_id', 'group_id', 'user_id',
    'thumbnail', 'youtube', 'rutube', 'dzen', 'vkVideo',
    'audio_file', 'audio_filename', 'audio_size', 'audio_mime', 'audio',
    'text', 'text_file', 'text_filename', 'text_size', 'text_mime',
    'views_count', 'likes_count',
])]
class Post extends Model
{
    use HasFactory, HasStorageUrls;

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'audio_size' => 'integer',
            'text_size' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)
            ->where('is_approved', true)
            ->whereNull('parent_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(PostStat::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    protected function audioUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->storageUrl($this->audio_file),
        );
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->storageUrl($this->thumbnail),
        );
    }

    protected function textFileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->storageUrl($this->text_file),
        );
    }
}
