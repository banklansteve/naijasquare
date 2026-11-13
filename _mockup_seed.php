<?php

use App\Models\Community;
use App\Models\Community as C;
use App\Models\Post;
use App\Models\PostReply;
use Carbon\Carbon;

$sentinel = 'Help tailoring my CV for NHS Band 5 role';

$run = Community::where('name', 'Nigerians in Sunderland')->firstOrFail();
$other = Community::where('name', 'Christian mothers in Sunderland')->firstOrFail();

$owner = $run->members()->wherePivot('role', C::ROLE_OWNER)->first();
if (! $owner) {
    $owner = $run->members()->first();
    $run->members()->syncWithoutDetaching([$owner->id => ['role' => C::ROLE_OWNER, 'guidelines_accepted_at' => now()]]);
}

// Ensure the owner also simply belongs to another community (for the "you're in" list).
$other->members()->syncWithoutDetaching([$owner->id => ['role' => C::ROLE_MEMBER, 'guidelines_accepted_at' => now()]]);

$owner->forceFill([
    'help_category_keys' => ['jobs', 'housing', 'immigration'],
    'help_note' => 'CV reviews, mock interviews, and settling-in tips',
])->save();

echo 'login_email='.$owner->email.PHP_EOL;
echo 'login_password=password'.PHP_EOL;
echo 'run_slug='.$run->slug.PHP_EOL;
echo 'owner_id='.$owner->id.PHP_EOL;

if (Post::where('title', $sentinel)->exists()) {
    echo 'already_seeded=1'.PHP_EOL;

    return;
}

$members = $run->members()->wherePivot('role', '!=', C::ROLE_OWNER)->get()->values();
$pick = fn (int $i) => $members[$i % max(1, $members->count())];

/** @var array<int, array{community:Community, author:mixed, type:string, cat:array, title:string, body:string, urgency:string, loc:array, ago:Carbon, resolved:bool, replies:array}> $rows */
$rows = [
    [
        'community' => $run, 'author' => $pick(0), 'type' => Post::TYPE_NEED,
        'cat' => ['housing'], 'title' => 'Room needed near Sunderland centre from next month',
        'body' => 'Working full-time on the wards, quiet and tidy. Looking for a single room, ideally close to the Royal Hospital. Any leads appreciated.',
        'urgency' => 'urgent', 'loc' => ['Sunderland'], 'ago' => now()->subHours(2), 'resolved' => false, 'replies' => [],
    ],
    [
        'community' => $run, 'author' => $pick(1), 'type' => Post::TYPE_NEED,
        'cat' => ['jobs'], 'title' => $sentinel,
        'body' => 'I have an interview next week and want my CV to match the NHS competencies. Could someone who has done this look over it?',
        'urgency' => 'soon', 'loc' => ['Sunderland'], 'ago' => now()->subHours(5), 'resolved' => false,
        'replies' => [
            [$pick(3), 'Happy to help — I reviewed a few of these last year. Share it in the thread and I will add comments.'],
            [$pick(4), 'Also look at the person specification and mirror the wording. Made a big difference for me.'],
        ],
    ],
    [
        'community' => $run, 'author' => $pick(2), 'type' => Post::TYPE_NEED,
        'cat' => ['immigration'], 'title' => 'Spouse visa extension — which documents do I need?',
        'body' => 'Getting ready to apply for the extension and want to make sure I have the right paperwork before booking. Has anyone been through this recently?',
        'urgency' => 'normal', 'loc' => ['Sunderland'], 'ago' => now()->subDay(), 'resolved' => false,
        'replies' => [
            [$pick(5), 'I kept a checklist from mine — will paste it below. Remember this is just what worked for me, not legal advice.'],
        ],
    ],
    [
        'community' => $run, 'author' => $pick(6), 'type' => Post::TYPE_OFFER,
        'cat' => ['healthcare'], 'title' => 'I can help you register with a GP',
        'body' => 'New to the area? I can walk you through registering with a local GP and what to bring. Message in the thread.',
        'urgency' => 'normal', 'loc' => ['Sunderland'], 'ago' => now()->subDays(1)->subHours(3), 'resolved' => false, 'replies' => [],
    ],
    [
        'community' => $run, 'author' => $pick(7), 'type' => Post::TYPE_NEED,
        'cat' => ['childcare'], 'title' => 'After-school pickup help on Tuesdays',
        'body' => 'Looking for a trusted parent to help with a 3:15 pickup on Tuesdays near Hendon. Happy to return the favour another day.',
        'urgency' => 'normal', 'loc' => ['Sunderland'], 'ago' => now()->subDays(3), 'resolved' => true, 'replies' => [],
    ],
    [
        'community' => $run, 'author' => $owner, 'type' => Post::TYPE_OFFER,
        'cat' => ['jobs'], 'title' => 'Happy to review CVs and run mock interviews',
        'body' => 'I hire in my team and am glad to give honest feedback on CVs or do a 20-minute mock interview. Evenings work best.',
        'urgency' => 'normal', 'loc' => ['Sunderland'], 'ago' => now()->subHours(6), 'resolved' => false, 'replies' => [],
    ],
    [
        'community' => $run, 'author' => $owner, 'type' => Post::TYPE_NEED,
        'cat' => ['money'], 'title' => 'Best bank for a new arrival with no credit history?',
        'body' => 'A cousin just moved over and is struggling to open an account. Which banks were easiest for people here?',
        'urgency' => 'normal', 'loc' => ['Sunderland'], 'ago' => now()->subDay()->subHours(2), 'resolved' => false, 'replies' => [],
    ],
];

foreach ($rows as $row) {
    /** @var Post $post */
    $post = Post::factory()
        ->categories($row['cat'])
        ->locations($row['loc'])
        ->state([
            'community_id' => $row['community']->id,
            'user_id' => $row['author']->id,
            'type' => $row['type'],
            'title' => $row['title'],
            'body' => $row['body'],
            'urgency' => $row['urgency'],
            'resolved_at' => $row['resolved'] ? $row['ago']->copy()->addHours(6) : null,
            'expires_at' => now()->addDays(14),
        ])
        ->create();

    $post->forceFill(['created_at' => $row['ago'], 'updated_at' => $row['ago']])->save();

    foreach ($row['replies'] as $i => [$replier, $body]) {
        $reply = PostReply::factory()->state([
            'post_id' => $post->id,
            'user_id' => $replier->id,
            'body' => $body,
        ])->create();
        $when = $row['ago']->copy()->addHours($i + 1);
        $reply->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
    }
}

echo 'seeded_posts='.count($rows).PHP_EOL;
