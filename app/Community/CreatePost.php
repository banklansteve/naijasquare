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
     *     category_key?: string|null,
     *     category_keys?: list<string>|null,
     *     location_label?: string|null,
     *     location_labels?: list<string>|null,
     *     location_specific?: bool|null,
     *     urgency?: string|null,
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

        $type = $data['type'];
        $categoryKeys = $this->normalizeCategoryKeys($type, $data);
        $locationLabels = $this->normalizeLocationLabels($type, $data);
        $urgency = $type === Post::TYPE_OFFER
            ? 'normal'
            : (string) ($data['urgency'] ?? 'normal');
        $defaultDays = (int) config("posts.urgencies.{$urgency}.default_days", 14);
        $expiresAt = isset($data['expires_at']) && filled($data['expires_at'])
            ? $data['expires_at']
            : now()->addDays($defaultDays);

        return Post::query()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $data['title'],
            'body' => $data['body'],
            'category_key' => $categoryKeys[0],
            'category_keys' => $categoryKeys,
            'location_label' => $locationLabels[0] ?? null,
            'location_labels' => $locationLabels,
            'urgency' => $urgency,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private function normalizeCategoryKeys(string $type, array $data): array
    {
        if ($type === Post::TYPE_OFFER) {
            $keys = collect($data['category_keys'] ?? [])
                ->filter(fn ($key): bool => is_string($key) && $key !== '')
                ->unique()
                ->take(3)
                ->values()
                ->all();

            return array_values($keys);
        }

        $key = is_string($data['category_key'] ?? null) ? $data['category_key'] : '';

        return [$key];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private function normalizeLocationLabels(string $type, array $data): array
    {
        if ($type === Post::TYPE_OFFER) {
            $locationSpecific = (bool) ($data['location_specific'] ?? false);

            if (! $locationSpecific) {
                return [];
            }

            return collect($data['location_labels'] ?? [])
                ->filter(fn ($label): bool => is_string($label) && trim($label) !== '')
                ->map(fn (string $label): string => trim($label))
                ->unique()
                ->take(3)
                ->values()
                ->all();
        }

        $label = is_string($data['location_label'] ?? null) ? trim($data['location_label']) : '';

        return $label !== '' ? [$label] : [];
    }
}
