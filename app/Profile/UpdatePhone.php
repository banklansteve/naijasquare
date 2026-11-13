<?php

namespace App\Profile;

use App\Models\User;
use App\Signup\UkMobile;

class UpdatePhone
{
    public function handle(User $user, ?string $phone): User
    {
        $normalised = null;

        if (is_string($phone) && trim($phone) !== '') {
            $normalised = UkMobile::normalise(trim($phone));
        }

        $user->forceFill([
            'phone' => $normalised,
        ])->save();

        return $user->refresh();
    }
}
