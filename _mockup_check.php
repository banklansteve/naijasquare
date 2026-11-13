<?php

use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;

echo 'users='.User::count().PHP_EOL;
echo 'communities='.Community::count().PHP_EOL;
echo 'posts='.Post::count().PHP_EOL;
echo 'replies='.PostReply::count().PHP_EOL;

$ada = User::where('email', 'ada.okeke.01@email.com')->first();

if ($ada) {
    echo 'ada_id='.$ada->id.PHP_EOL;
    echo 'ada_city='.$ada->city.PHP_EOL;
    echo 'ada_communities='.$ada->communities()->count().PHP_EOL;
    echo 'ada_help='.json_encode($ada->help_category_keys).PHP_EOL;
    foreach ($ada->communities as $c) {
        echo '  - '.$c->id.' '.$c->name.' role='.$c->pivot->role.PHP_EOL;
    }
} else {
    echo 'ada=none'.PHP_EOL;
}
