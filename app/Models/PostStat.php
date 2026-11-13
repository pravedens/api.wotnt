<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'post_id', 'user_id', 'ip', 'user_agent', 'fingerprint',
    'viewed', 'liked', 'viewed_at', 'viewed_at_date',
])]
class PostStat extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'viewed' => 'boolean',
            'liked' => 'boolean',
            'viewed_at' => 'datetime',
            'viewed_at_date' => 'date',
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
}
