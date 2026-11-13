<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'post_id', 'user_id', 'parent_id', 'content', 'is_approved', 'likes_count',
])]
class PostComment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'likes_count' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_id')
            ->where('is_approved', true);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }
}
