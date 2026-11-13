<?php

namespace App\Profile;

use App\Models\User;
use App\Signup\EmailVerificationCodes;

class UpdateEmail
{
    public function __construct(private EmailVerificationCodes $codes) {}

    public function handle(User $user, string $email): User
    {
        $email = strtolower(trim($email));

        if ($email === strtolower((string) $user->email)) {
            return $user;
        }

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => null,
        ])->save();

        $this->codes->issue($user->refresh());

        return $user;
    }
}
