<?php

namespace App\Home;

use App\Models\User;

class UpdateHelpWith
{
    /**
     * @param  list<string>  $categoryKeys
     */
    public function handle(User $user, array $categoryKeys, ?string $note): User
    {
        $user->forceFill([
            'help_category_keys' => array_values(array_unique($categoryKeys)),
            'help_note' => $note !== null && $note !== '' ? $note : null,
        ])->save();

        return $user->refresh();
    }
}
