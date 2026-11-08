<?php

namespace App\Community;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReplyToPost
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function handle(User $user, Post $post, string $body): PostReply
    {
        $community = $post->community;

        if (! $this->authorizer->canReply($user, $community)) {
            throw ValidationException::withMessages([
                'body' => 'You cannot reply in this community right now.',
            ]);
        }

        if ((int) $post->user_id === (int) $user->id) {
            $label = $post->isOffer() ? 'Offer' : 'Need';

            throw ValidationException::withMessages([
                'body' => "You cannot reply on your own {$label}. Wait for someone else to reply.",
            ]);
        }

        if ($post->isHidden()) {
            throw ValidationException::withMessages([
                'body' => 'This post is no longer open for replies.',
            ]);
        }

        return PostReply::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);
    }
}
