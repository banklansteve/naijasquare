<?php

namespace App\Models;

use App\Community\CommunityAuthorizer;
use App\Support\PublicUid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'uid',
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'password',
    'house_number',
    'postcode',
    'address_line',
    'place_label',
    'city',
    'location_display',
    'profile_visibility',
    'life_stage',
    'interests',
    'help_category_keys',
    'help_note',
    'help_visibility',
    'photo_path',
    'bio',
    'notify_needs_replies',
    'notify_community',
    'notify_admin',
    'prefer_product_emails',
    'terms_accepted_at',
])]
#[Hidden([
    'password',
    'remember_token',
    'phone',
    'house_number',
    'postcode',
    'address_line',
    'photo_path',
    'invited_by_user_id',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const LOCATION_DISPLAY_PLACE = 'place';

    public const LOCATION_DISPLAY_CITY = 'city';

    public const LOCATION_DISPLAY_HIDDEN = 'hidden';

    public const VISIBILITY_COMMUNITIES = 'communities';

    public const VISIBILITY_PRIVATE = 'private';

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->uid === null || $user->uid === '') {
                $user->uid = PublicUid::make();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'home_checklist_dismissed_at' => 'datetime',
            'interests' => 'array',
            'help_category_keys' => 'array',
            'notify_needs_replies' => 'boolean',
            'notify_community' => 'boolean',
            'notify_admin' => 'boolean',
            'prefer_product_emails' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Coarse location shown to others — never house number or postcode.
     */
    public function displayLocation(): ?string
    {
        if ($this->location_display === self::LOCATION_DISPLAY_HIDDEN) {
            return null;
        }

        if ($this->location_display === self::LOCATION_DISPLAY_CITY) {
            return filled($this->city) ? (string) $this->city : null;
        }

        if (filled($this->place_label)) {
            return (string) $this->place_label;
        }

        return filled($this->city) ? (string) $this->city : null;
    }

    public function wantsNotification(string $type): bool
    {
        return match ($type) {
            'related_need' => (bool) $this->notify_needs_replies,
            'community_admin_invite' => (bool) $this->notify_admin,
            default => (bool) $this->notify_community,
        };
    }

    /**
     * @return BelongsToMany<Community, $this>
     */
    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)
            ->withPivot(['role', 'guidelines_accepted_at', 'pinned_at'])
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /**
     * @return HasMany<AppNotification, $this>
     */
    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function canInCommunity(string $permission, Community $community): bool
    {
        return app(CommunityAuthorizer::class)->can($this, $permission, $community);
    }

    public function roleInCommunity(Community $community): ?string
    {
        return app(CommunityAuthorizer::class)->roleIn($this, $community);
    }

    public function isMemberOf(Community $community): bool
    {
        return app(CommunityAuthorizer::class)->isMember($this, $community);
    }
}
