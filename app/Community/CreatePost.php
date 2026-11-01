<?php

namespace App\Community;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreatePost
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    /**
     * @param  array{
     *     type: string,
     *     title: string,
     *     body: string,
     *     category_key: string,
     *     location_label: ?string,
     *     urgency: string,
     *     expires_at?: ?string
     * }  $data
     */
    public function handle(User $user, Community $community, array $data): Post
    {
        if (! $this->authorizer->canCreatePost($user, $community)) {
            throw ValidationException::withMessages([
                'post' => 'You cannot create a post in this community right now.',
            ]);
        }

        $urgency = $data['urgency'];
        $defaultDays = (int) config("posts.urgencies.{$urgency}.default_days", 14);
        $expiresAt = isset($data['expires_at']) && filled($data['expires_at'])
            ? $data['expires_at']
            : now()->addDays($defaultDays);

        return Post::query()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'category_key' => $data['category_key'],
            'location_label' => ($data['location_label'] ?? null) ?: $community->location_label,
            'urgency' => $urgency,
            'expires_at' => $expiresAt,
        ]);
    }
}
