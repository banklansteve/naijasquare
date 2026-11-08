<?php

namespace App\Community;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;

class FindRelatedOffers
{
    /**
     * Open Offers in this community that may help with a Need.
     *
     * @return list<array<string, mixed>>
     */
    public function forNeed(
        Community $community,
        string $categoryKey,
        ?string $locationLabel = null,
        ?User $viewer = null,
        int $limit = 5,
    ): array {
        $location = is_string($locationLabel) && trim($locationLabel) !== ''
            ? strtolower(trim($locationLabel))
            : null;

        $offers = Post::query()
            ->with(['author:id,name'])
            ->where('community_id', $community->id)
            ->where('type', Post::TYPE_OFFER)
            ->whereNull('resolved_at')
            ->whereNull('hidden_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) use ($categoryKey): void {
                $query->where('category_key', $categoryKey)
                    ->orWhereJsonContains('category_keys', $categoryKey);
            })
            ->when(
                $viewer !== null,
                fn ($query) => $query->where('user_id', '!=', $viewer->id),
            )
            ->latest()
            ->limit(20)
            ->get();

        return $offers
            ->sortByDesc(function (Post $offer) use ($location): array {
                $offerLocations = collect($offer->locationLabels())
                    ->map(fn (string $label): string => strtolower($label))
                    ->all();

                $locationScore = 0;
                if ($offerLocations === []) {
                    // Non-location-specific Offers still match; prefer area overlap when present.
                    $locationScore = $location === null ? 1 : 0;
                } elseif ($location !== null) {
                    foreach ($offerLocations as $offerLocation) {
                        if (
                            $offerLocation === $location
                            || str_contains($offerLocation, $location)
                            || str_contains($location, $offerLocation)
                        ) {
                            $locationScore = 1;
                            break;
                        }
                    }
                }

                return [
                    $locationScore,
                    $offer->created_at?->timestamp ?? 0,
                ];
            })
            ->take($limit)
            ->values()
            ->map(fn (Post $offer): array => $this->toCard($offer, $community))
            ->all();
    }

    /**
     * Unique Offer authors who should hear about a new Need.
     *
     * @return Collection<int, User>
     */
    public function authorsToNotify(Post $need, int $limit = 10): Collection
    {
        if ($need->type !== Post::TYPE_NEED || $need->community === null) {
            return collect();
        }

        $cards = $this->forNeed(
            $need->community,
            $need->category_key,
            $need->location_label,
            $need->author,
            limit: 20,
        );

        $authorIds = collect($cards)
            ->pluck('author.id')
            ->filter()
            ->unique()
            ->take($limit)
            ->values();

        if ($authorIds->isEmpty()) {
            return collect();
        }

        return User::query()->whereIn('id', $authorIds)->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function toCard(Post $offer, Community $community): array
    {
        $locations = $offer->locationLabels();

        return [
            'id' => $offer->id,
            'title' => $offer->title,
            'body' => $offer->body,
            'category_key' => $offer->category_key,
            'category_keys' => $offer->categoryKeys(),
            'category_label' => $offer->categoryLabel(),
            'category_labels' => $offer->categoryLabels(),
            'location_label' => $locations[0] ?? null,
            'location_labels' => $locations,
            'location_specific' => $locations !== [],
            'href' => '/communities/'.$community->slug.'/posts/'.$offer->id,
            'author' => [
                'id' => $offer->author?->id,
                'name' => $offer->author?->name ?? 'Member',
            ],
        ];
    }
}
