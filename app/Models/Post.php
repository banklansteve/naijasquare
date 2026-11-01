<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'community_id',
    'user_id',
    'type',
    'title',
    'body',
    'category_key',
    'location_label',
    'urgency',
    'expires_at',
    'resolved_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const TYPE_NEED = 'need';

    public const TYPE_OFFER = 'offer';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<PostReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(PostReply::class);
    }

    /**
     * @return MorphMany<Report, $this>
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function isNeed(): bool
    {
        return $this->type === self::TYPE_NEED;
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function categoryLabel(): string
    {
        return (string) config("posts.categories.{$this->category_key}.label", $this->category_key);
    }

    public function typeLabel(): string
    {
        return (string) config("posts.types.{$this->type}", $this->type);
    }

    public function urgencyLabel(): string
    {
        return (string) config("posts.urgencies.{$this->urgency}.label", $this->urgency);
    }

    public function requiresSafetyWarning(): bool
    {
        return (bool) config("posts.categories.{$this->category_key}.safety_warning", false);
    }
}
