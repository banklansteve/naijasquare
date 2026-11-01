<?php

namespace App\Community;

use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResolvePost
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function handle(User $user, Post $post): Post
    {
        if (! $this->authorizer->canResolve($user, $post)) {
            throw ValidationException::withMessages([
                'post' => 'Only the person who posted this can mark it resolved.',
            ]);
        }

        if ($post->isResolved()) {
            return $post;
        }

        $post->update(['resolved_at' => now()]);

        return $post->fresh();
    }
}
