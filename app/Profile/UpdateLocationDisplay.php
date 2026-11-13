<?php

namespace App\Profile;

use App\Models\User;

class UpdateLocationDisplay
{
    public function handle(User $user, string $locationDisplay): User
    {
        $user->forceFill([
            'location_display' => $locationDisplay,
        ])->save();

        return $user->refresh();
    }
}
