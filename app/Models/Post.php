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
    'category_keys',
    'location_label',
    'location_labels',
    'urgency',
    'expires_at',
    'resolved_at',
    'hidden_at',
    'hidden_by_user_id',
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
            'category_keys' => 'array',
            'location_labels' => 'array',
            'expires_at' => 'datetime',
            'resolved_at' => 'datetime',
            'hidden_at' => 'datetime',
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

    public function isOffer(): bool
    {
        return $this->type === self::TYPE_OFFER;
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * @return list<string>
     */
    public function categoryKeys(): array
    {
        $keys = collect($this->category_keys ?? [])
            ->filter(fn ($key): bool => is_string($key) && $key !== '')
            ->values()
            ->all();

        if ($keys === [] && filled($this->category_key)) {
            return [(string) $this->category_key];
        }

        return array_values($keys);
    }

    /**
     * @return list<string>
     */
    public function locationLabels(): array
    {
        $labels = collect($this->location_labels ?? [])
            ->filter(fn ($label): bool => is_string($label) && trim($label) !== '')
            ->map(fn (string $label): string => trim($label))
            ->values()
            ->all();

        if ($labels === [] && filled($this->location_label)) {
            return [(string) $this->location_label];
        }

        return array_values($labels);
    }

    public function isLocationSpecific(): bool
    {
        return $this->locationLabels() !== [];
    }

    public function categoryLabel(): string
    {
        return (string) config("posts.categories.{$this->category_key}.label", $this->category_key);
    }

    /**
     * @return list<string>
     */
    public function categoryLabels(): array
    {
        return collect($this->categoryKeys())
            ->map(fn (string $key): string => (string) config("posts.categories.{$key}.label", $key))
            ->values()
            ->all();
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
        foreach ($this->categoryKeys() as $key) {
            if ((bool) config("posts.categories.{$key}.safety_warning", false)) {
                return true;
            }
        }

        return (bool) config("posts.categories.{$this->category_key}.safety_warning", false);
    }
}
