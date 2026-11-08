<?php

namespace App\Community;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MarkReplyHelped
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function mark(User $user, PostReply $reply): PostReply
    {
        $post = $this->assertCanMark($user, $reply);

        if ($reply->helped_at !== null) {
            return $reply;
        }

        $reply->forceFill([
            'helped_at' => now(),
            'helped_by_user_id' => $user->id,
        ])->save();

        return $reply->refresh();
    }

    public function unmark(User $user, PostReply $reply): PostReply
    {
        $this->assertCanMark($user, $reply);

        if ($reply->helped_at === null) {
            return $reply;
        }

        $reply->forceFill([
            'helped_at' => null,
            'helped_by_user_id' => null,
        ])->save();

        return $reply->refresh();
    }

    private function assertCanMark(User $user, PostReply $reply): Post
    {
        $reply->loadMissing(['post.community']);
        $post = $reply->post;

        if ($post === null || ! $post->isNeed()) {
            throw ValidationException::withMessages([
                'reply' => 'Only replies on a Need can be marked as helpful.',
            ]);
        }

        if ((int) $post->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'reply' => 'Only the person who asked can mark a reply as helpful.',
            ]);
        }

        if ($post->isHidden() || $reply->isHidden()) {
            throw ValidationException::withMessages([
                'reply' => 'This reply cannot be marked right now.',
            ]);
        }

        if ((int) $reply->user_id === (int) $user->id) {
            throw ValidationException::withMessages([
                'reply' => 'You cannot mark your own reply as helpful.',
            ]);
        }

        $community = $post->community;
        if ($community === null || ! $this->authorizer->isMember($user, $community)) {
            throw ValidationException::withMessages([
                'reply' => 'You cannot mark replies in this community right now.',
            ]);
        }

        return $post;
    }
}
