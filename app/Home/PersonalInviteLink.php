<?php

namespace App\Home;

use App\Models\User;
use App\Support\SignupRoute;

class PersonalInviteLink
{
    /**
     * @return array{url: string, message: string, whatsapp_url: string}
     */
    public function for(User $user): array
    {
        $url = route(SignupRoute::NAME, [
            'invited_by' => $user->uid,
        ], absolute: true);

        $appName = (string) config('app.name');
        $first = $user->first_name !== null && $user->first_name !== ''
            ? $user->first_name
            : $user->name;

        $message = "Join me on {$appName} — {$first} invited you: {$url}";

        return [
            'url' => $url,
            'message' => $message,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($message),
        ];
    }
}
