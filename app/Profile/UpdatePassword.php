<?php

namespace App\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePassword
{
    public function handle(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'That password does not match our records.',
            ]);
        }

        $user->forceFill([
            'password' => $newPassword,
        ])->save();
    }
}
