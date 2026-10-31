<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
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
        return $this->belongsToMany(Community::class)->withTimestamps();
    }
}
