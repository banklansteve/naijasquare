<?php

namespace App\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeleteAccount
{
    public const ANONYMOUS_NAME = 'Former member';

    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            if (is_string($user->photo_path) && $user->photo_path !== '') {
                Storage::disk('local')->delete($user->photo_path);
            }

            $user->forceFill([
                'name' => self::ANONYMOUS_NAME,
                'first_name' => 'Former',
                'last_name' => 'member',
                'email' => 'deleted+'.$user->uid.'@deleted.invalid',
                'email_verified_at' => null,
                'phone' => null,
                'bio' => null,
                'photo_path' => null,
                'house_number' => null,
                'postcode' => null,
                'address_line' => null,
                'place_label' => null,
                'city' => null,
                'help_category_keys' => null,
                'help_note' => null,
                'password' => Str::password(40),
            ])->save();

            $user->delete();
        });
    }
}
