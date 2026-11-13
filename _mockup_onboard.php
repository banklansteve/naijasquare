<?php

use App\Models\Community;
use App\Models\Community as C;
use App\Models\User;

$user = User::where('email', 'ngozi.eze.05@email.com')->firstOrFail();

$run = Community::where('name', 'Nigerians in Sunderland')->firstOrFail();
$other = Community::where('name', 'Christian mothers in Sunderland')->firstOrFail();

$run->members()->syncWithoutDetaching([$user->id => ['role' => C::ROLE_MEMBER, 'guidelines_accepted_at' => now()]]);
$other->members()->syncWithoutDetaching([$user->id => ['role' => C::ROLE_MEMBER, 'guidelines_accepted_at' => now()]]);

$user->forceFill([
    'help_category_keys' => null,
    'help_note' => null,
    'home_checklist_dismissed_at' => null,
])->save();

echo 'onboard_email='.$user->email.PHP_EOL;
echo 'onboard_communities='.$user->communities()->count().PHP_EOL;
