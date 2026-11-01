<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

final class PublicUid
{
    public static function make(): string
    {
        do {
            $uid = Str::upper(Str::random(8));
        } while (User::query()->where('uid', $uid)->exists());

        return $uid;
    }
}
