<?php

namespace App\Signup;

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Collection;

class CommunitySuggestions
{
    /**
     * Real communities only. A community with no members is left out.
     *
     * @return Collection<int, array{id: int, name: string, description: string, members: int}>
     */
    public function for(User $user): Collection
    {
        if ($user->city === null || $user->city === '') {
            return collect();
        }

        $interests = $user->interests ?? [];

        $joined = $user->communities()->pluck('communities.id');

        return Community::query()
            ->where('listed', true)
            ->whereRaw('lower(city) = ?', [strtolower($user->city)])
            ->when($joined->isNotEmpty(), fn ($query) => $query->whereNotIn('communities.id', $joined))
            ->has('members')
            ->withCount('members')
            ->get()
            ->filter(function (Community $community) use ($interests): bool {
                $keys = $community->interest_keys ?? [];

                if ($keys === []) {
                    return true;
                }

                return array_intersect($keys, $interests) !== [];
            })
            ->sortByDesc('members_count')
            ->take(6)
            ->values()
            ->map(fn (Community $community): array => [
                'id' => $community->id,
                'name' => $community->name,
                'description' => $community->description,
                'members' => (int) $community->members_count,
            ]);
    }

    /**
     * @return list<int>
     */
    public function idsFor(User $user): array
    {
        return $this->for($user)->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }
}
