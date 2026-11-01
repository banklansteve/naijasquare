<?php

namespace App\Models;

use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'description',
    'city',
    'interest_keys',
    'listed',
    'access_mode',
    'location_type',
    'location_label',
    'tag_keys',
    'guidelines',
    'require_guidelines_accept',
    'who_can_post',
    'member_list_visibility',
    'cover_path',
    'admin_digest_enabled',
    'created_by',
])]
class Community extends Model
{
    /** @use HasFactory<CommunityFactory> */
    use HasFactory;

    public const ACCESS_OPEN = 'open';

    public const ACCESS_REQUEST = 'request';

    public const ACCESS_INVITE_ONLY = 'invite_only';

    public const ROLE_OWNER = 'owner';

    public const ROLE_MODERATOR = 'moderator';

    public const ROLE_MEMBER = 'member';

    protected static function booted(): void
    {
        static::creating(function (Community $community): void {
            if ($community->slug === null || $community->slug === '') {
                $community->slug = static::uniqueSlug($community->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'community';
        $slug = $base;
        $n = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interest_keys' => 'array',
            'tag_keys' => 'array',
            'listed' => 'boolean',
            'require_guidelines_accept' => 'boolean',
            'admin_digest_enabled' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'guidelines_accepted_at'])
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<CommunityJoinRequest, $this>
     */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany<CommunityAdminInvitation, $this>
     */
    public function adminInvitations(): HasMany
    {
        return $this->hasMany(CommunityAdminInvitation::class);
    }

    public function accessModeLabel(): string
    {
        return (string) config("community.access_modes.{$this->access_mode}.label", $this->access_mode);
    }

    public function coverUrl(): ?string
    {
        if (! is_string($this->cover_path) || $this->cover_path === '') {
            return null;
        }

        return route('communities.cover', $this);
    }

    /**
     * @return list<string>
     */
    public function cardTagKeys(): array
    {
        $tags = $this->tag_keys ?? [];
        $interests = $this->interest_keys ?? [];

        return array_values(array_unique([...$tags, ...$interests]));
    }
}
