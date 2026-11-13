<?php

namespace App\Models;

use Database\Factories\PostReplyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'post_id',
    'user_id',
    'body',
    'helped_at',
    'helped_by_user_id',
    'hidden_at',
    'hidden_by_user_id',
])]
class PostReply extends Model
{
    /** @use HasFactory<PostReplyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'helped_at' => 'datetime',
            'hidden_at' => 'datetime',
        ];
    }

    public function isHelped(): bool
    {
        return $this->helped_at !== null;
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
