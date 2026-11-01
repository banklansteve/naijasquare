<?php

namespace App\Community;

use App\Models\Community;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicCommunityQuery
{
    /**
     * @return Builder<Community>
     */
    public function base(): Builder
    {
        return Community::query()
            ->where('listed', true)
            ->where('access_mode', '!=', Community::ACCESS_INVITE_ONLY)
            ->has('members')
            ->withCount('members');
    }

    public function isPubliclyVisible(Community $community): bool
    {
        if (! $community->listed) {
            return false;
        }

        if ($community->access_mode === Community::ACCESS_INVITE_ONLY) {
            return false;
        }

        return $community->members()->exists();
    }

    /**
     * @return Builder<Community>
     */
    public function forPlace(string $placeSlug): Builder
    {
        $slug = Str::slug($placeSlug);

        if ($slug === 'uk') {
            return $this->base()->where('location_type', 'uk_wide');
        }

        return $this->base()->where(function (Builder $query) use ($slug): void {
            $query
                ->whereRaw('lower(communities.city) = ?', [$this->slugToLabelCandidate($slug)])
                ->orWhereRaw('lower(communities.location_label) = ?', [$this->slugToLabelCandidate($slug)])
                ->orWhereRaw("replace(lower(communities.city), ' ', '-') = ?", [$slug])
                ->orWhereRaw("replace(lower(coalesce(communities.location_label, '')), ' ', '-') = ?", [$slug]);
        });
    }

    /**
     * @return Builder<Community>
     */
    public function forTag(string $tag): Builder
    {
        return $this->base()->where(function (Builder $query) use ($tag): void {
            $query
                ->whereJsonContains('communities.tag_keys', $tag)
                ->orWhereJsonContains('communities.interest_keys', $tag);
        });
    }

    /**
     * @return Collection<int, array{slug: string, label: string, count: int}>
     */
    public function places(): Collection
    {
        $communities = $this->base()
            ->get(['id', 'city', 'location_label', 'location_type']);

        $places = collect();

        foreach ($communities as $community) {
            if ($community->location_type === 'uk_wide') {
                $key = 'uk';
                $label = 'UK-wide';
            } else {
                $label = filled($community->city)
                    ? (string) $community->city
                    : (string) ($community->location_label ?: 'Unknown');
                $key = Str::slug($label);
            }

            if ($key === '') {
                continue;
            }

            if (! $places->has($key)) {
                $places->put($key, [
                    'slug' => $key,
                    'label' => $label,
                    'count' => 0,
                ]);
            }

            $places[$key] = [
                ...$places[$key],
                'count' => $places[$key]['count'] + 1,
            ];
        }

        return $places
            ->values()
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @return Collection<int, array{key: string, label: string, count: int}>
     */
    public function tags(): Collection
    {
        $configTags = config('community.tags', []);
        $communities = $this->base()->get(['id', 'tag_keys', 'interest_keys']);

        $counts = collect($configTags)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'count' => 0,
            ]);

        foreach ($communities as $community) {
            foreach ($community->cardTagKeys() as $key) {
                if (! $counts->has($key)) {
                    continue;
                }

                $row = $counts->get($key);
                $row['count']++;
                $counts->put($key, $row);
            }
        }

        return $counts
            ->filter(fn (array $row): bool => $row['count'] > 0)
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @return array{id: int, slug: string, name: string, description: string, members: int, access_mode: string, access_label: string, location_label: ?string, cover_url: ?string, tag_keys: list<string>, tags: list<array{key: string, label: string}>}
     */
    public function toCard(Community $community): array
    {
        $tagKeys = $community->cardTagKeys();
        $tags = collect($tagKeys)
            ->map(function (string $key): ?array {
                $label = config("community.tags.{$key}");

                if (! is_string($label) || $label === '') {
                    return null;
                }

                return ['key' => $key, 'label' => $label];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'description' => $community->description,
            'members' => (int) ($community->members_count ?? $community->members()->count()),
            'access_mode' => $community->access_mode,
            'access_label' => $community->accessModeLabel(),
            'location_label' => $community->location_label,
            'cover_url' => $community->coverUrl(),
            'tag_keys' => $tagKeys,
            'tags' => $tags,
        ];
    }

    public function placeLabel(string $placeSlug): ?string
    {
        $slug = Str::slug($placeSlug);

        if ($slug === 'uk') {
            return 'UK-wide';
        }

        $match = $this->forPlace($slug)->orderBy('name')->first();

        if ($match === null) {
            return null;
        }

        return filled($match->city) ? (string) $match->city : (string) ($match->location_label ?: Str::title(str_replace('-', ' ', $slug)));
    }

    private function slugToLabelCandidate(string $slug): string
    {
        return strtolower(str_replace('-', ' ', $slug));
    }
}
