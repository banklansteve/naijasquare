<?php

namespace App\Models;

use App\Community\CommunityAuthorizer;
use App\Support\PublicUid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    'life_stage',
    'interests',
    'photo_path',
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
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'interests' => 'array',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<Community, $this>
     */
    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)
            ->withPivot(['role', 'guidelines_accepted_at'])
            ->withTimestamps();
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
