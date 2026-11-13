<?php

namespace App\Profile;

use App\Models\User;

class UpdatePrivacyPreferences
{
    /**
     * @param  array{
     *     profile_visibility: string,
     *     help_visibility: string,
     *     location_display: string,
     *     notify_needs_replies: bool,
     *     notify_community: bool,
     *     notify_admin: bool,
     *     prefer_product_emails: bool
     * }  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->forceFill([
            'profile_visibility' => $data['profile_visibility'],
            'help_visibility' => $data['help_visibility'],
            'location_display' => $data['location_display'],
            'notify_needs_replies' => $data['notify_needs_replies'],
            'notify_community' => $data['notify_community'],
            'notify_admin' => $data['notify_admin'],
            'prefer_product_emails' => $data['prefer_product_emails'],
        ])->save();

        return $user->refresh();
    }
}
