<?php

namespace App\Signup;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommunitySuggestions
{
    /**
     * Real communities only. A community with no members is left out.
     * Invite-only communities are never suggested for open join.
     * Matches the member's city (or UK-wide) and interests/tags.
     *
     * @return Collection<int, array{id: int, slug: string, name: string, description: string, members: int, access_mode: string, cover_url: ?string, tag_keys: list<string>}>
     */
    public function for(User $user, int $limit = 6): Collection
    {
        return $this->relevant($user)
            ->take($limit)
            ->values()
            ->map(fn (Community $community): array => $this->toCard($community));
    }

    /**
     * Broader discover set for the communities browse page.
     *
     * @return Collection<int, Community>
     */
    public function discoverQuery(User $user): Builder
    {
        $joined = $user->communities()->pluck('communities.id');

        return Community::query()
            ->where('listed', true)
            ->where('access_mode', '!=', Community::ACCESS_INVITE_ONLY)
            ->when($joined->isNotEmpty(), fn ($query) => $query->whereNotIn('communities.id', $joined))
            ->where(function (Builder $query) use ($user): void {
                $this->applyLocationRelevance($query, $user);
            })
            ->has('members')
            ->withCount('members');
    }

    /**
     * @return Collection<int, Community>
     */
    public function relevant(User $user): Collection
    {
        if (! $this->hasLocation($user)) {
            return collect();
        }

        $interests = $user->interests ?? [];

        return $this->discoverQuery($user)
            ->get()
            ->filter(fn (Community $community): bool => $this->matchesInterests($community, $interests))
            ->sortByDesc(function (Community $community) use ($interests): array {
                return [
                    $this->interestScore($community, $interests),
                    (int) $community->members_count,
                ];
            })
            ->values();
    }

    /**
     * @return list<int>
     */
    public function idsFor(User $user): array
    {
        return $this->for($user)->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    public function matchesInterests(Community $community, array $interests): bool
    {
        if ($interests === []) {
            return true;
        }

        $keys = $community->cardTagKeys();

        if ($keys === []) {
            return true;
        }

        return array_intersect($keys, $interests) !== [];
    }

    /**
     * @param  Builder<Community>  $query
     */
    public function applyLocationRelevance(Builder $query, User $user): void
    {
        if (! $this->hasLocation($user)) {
            $query->whereRaw('0 = 1');

            return;
        }

        $city = strtolower((string) $user->city);

        $query->where(function (Builder $location) use ($city): void {
            $location
                ->whereRaw('lower(communities.city) = ?', [$city])
                ->orWhere(function (Builder $byLabel) use ($city): void {
                    $byLabel
                        ->where(function (Builder $missingCity): void {
                            $missingCity
                                ->whereNull('communities.city')
                                ->orWhere('communities.city', '');
                        })
                        ->whereRaw('lower(coalesce(communities.location_label, "")) = ?', [$city]);
                })
                ->orWhere('communities.location_type', 'uk_wide');
        });
    }

    private function hasLocation(User $user): bool
    {
        return $user->city !== null && $user->city !== '';
    }

    /**
     * @param  list<string>  $interests
     */
    private function interestScore(Community $community, array $interests): int
    {
        if ($interests === []) {
            return 0;
        }

        return count(array_intersect($community->cardTagKeys(), $interests));
    }

    /**
     * @return array{id: int, slug: string, name: string, description: string, members: int, access_mode: string, cover_url: ?string, tag_keys: list<string>}
     */
    private function toCard(Community $community): array
    {
        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'description' => $community->description,
            'members' => (int) $community->members_count,
            'access_mode' => $community->access_mode,
            'cover_url' => $community->coverUrl(),
            'tag_keys' => $community->cardTagKeys(),
        ];
    }
}
