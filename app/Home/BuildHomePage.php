<?php

namespace App\Home;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use App\Signup\CommunitySuggestions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class BuildHomePage
{
    public function __construct(
        private CommunitySuggestions $suggestions,
        private PersonalInviteLink $invites,
        private CommunityMonogram $monograms,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, Request $request): array
    {
        $categoryFilter = $this->categoryFilter($request);
        $joinedCount = $user->communities()->count();
        $helpCategories = $user->help_category_keys ?? [];
        $hasAskedOrAnswered = Post::query()->where('user_id', $user->id)->exists()
            || PostReply::query()->where('user_id', $user->id)->exists();

        $joinedCommunityIds = $user->communities()->pluck('communities.id');
        $myPosts = $this->myActivePosts($user);
        $memberships = $this->membershipCards($user);
        $nearCommunities = $this->nearCommunities($user);

        return [
            'greeting' => $this->greeting(now()),
            'firstName' => $user->first_name,
            'city' => $user->city,
            'askHref' => $this->postCreateHref($user, Post::TYPE_NEED),
            'offerHref' => $this->postCreateHref($user, Post::TYPE_OFFER),
            'postHref' => $this->postCreateHref($user, Post::TYPE_NEED),
            'myPosts' => $myPosts->values()->all(),
            'helpableNeeds' => $this->helpableNeeds($user, $joinedCommunityIds, $categoryFilter)->values()->all(),
            'categoryFilter' => $categoryFilter,
            'categoryFilters' => $this->categoryFilterOptions(),
            'administeredCommunities' => $memberships['administered']->take(4)->values()->all(),
            'yourCommunities' => $memberships['your']->take(3)->values()->all(),
            'nearCommunities' => $nearCommunities['items'],
            'nearCommunitiesEmpty' => $nearCommunities['empty'],
            'invite' => $this->invites->for($user),
            'firstWeek' => $this->firstWeek(
                $user,
                $joinedCount,
                $helpCategories !== [],
                $hasAskedOrAnswered,
            ),
            'helpWith' => [
                'category_keys' => $helpCategories,
                'note' => $user->help_note,
                'options' => $this->categoryFilterOptions(),
            ],
        ];
    }

    public function greeting(Carbon $at): string
    {
        $hour = (int) $at->format('G');

        if ($hour < 12) {
            return 'Good morning';
        }

        if ($hour < 17) {
            return 'Good afternoon';
        }

        return 'Good evening';
    }

    private function postCreateHref(User $user, string $type): string
    {
        $community = $user->communities()->orderByPivot('created_at')->first();

        if ($community === null) {
            return '/communities';
        }

        return '/communities/'.$community->slug.'/posts/create?type='.$type;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function myActivePosts(User $user): Collection
    {
        return Post::query()
            ->with(['community:id,slug,name'])
            ->withCount(['replies' => fn ($query) => $query->whereNull('hidden_at')])
            ->where('user_id', $user->id)
            ->whereNull('resolved_at')
            ->whereNull('hidden_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->limit(2)
            ->get()
            ->map(fn (Post $post): array => $this->postCard($post));
    }

    /**
     * @param  Collection<int, int|string>  $joinedCommunityIds
     * @return Collection<int, array<string, mixed>>
     */
    private function helpableNeeds(User $user, Collection $joinedCommunityIds, ?string $categoryFilter): Collection
    {
        $helpCategories = $user->help_category_keys ?? [];
        $city = $user->city !== null && $user->city !== '' ? strtolower($user->city) : null;

        $posts = Post::query()
            ->with(['community:id,slug,name,city,location_label', 'author:id,name'])
            ->withCount(['replies' => fn ($query) => $query->whereNull('hidden_at')])
            ->where('type', Post::TYPE_NEED)
            ->where('user_id', '!=', $user->id)
            ->whereNull('resolved_at')
            ->whereNull('hidden_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) use ($joinedCommunityIds, $city): void {
                $query->where(function ($scope) use ($joinedCommunityIds): void {
                    if ($joinedCommunityIds->isEmpty()) {
                        $scope->whereRaw('0 = 1');
                    } else {
                        $scope->whereIn('community_id', $joinedCommunityIds);
                    }
                });

                if ($city !== null) {
                    $query->orWhere(function ($location) use ($city): void {
                        $location
                            ->whereRaw('lower(coalesce(location_label, "")) = ?', [$city])
                            ->orWhereHas('community', function ($community) use ($city): void {
                                $community
                                    ->whereRaw('lower(coalesce(city, "")) = ?', [$city])
                                    ->orWhereRaw('lower(coalesce(location_label, "")) = ?', [$city]);
                            });
                    });
                }
            })
            ->when(
                $categoryFilter !== null,
                fn ($query) => $query->where('category_key', $categoryFilter),
            )
            ->latest()
            ->limit(20)
            ->get();

        return $posts
            ->sortByDesc(function (Post $post) use ($helpCategories, $city): array {
                return [
                    in_array($post->category_key, $helpCategories, true) ? 1 : 0,
                    $post->replies_count === 0 ? 1 : 0,
                    $city !== null && (
                        strtolower((string) $post->location_label) === $city
                        || strtolower((string) $post->community?->city) === $city
                    ) ? 1 : 0,
                    match ($post->urgency) {
                        'urgent' => 3,
                        'normal' => 2,
                        default => 1,
                    },
                    $post->created_at?->timestamp ?? 0,
                ];
            })
            ->take(6)
            ->values()
            ->map(fn (Post $post): array => $this->postCard($post, matched: in_array($post->category_key, $helpCategories, true)));
    }

    /**
     * @return array{your: Collection<int, array<string, mixed>>, administered: Collection<int, array<string, mixed>>}
     */
    private function membershipCards(User $user): array
    {
        $communities = $user->communities()
            ->withCount([
                'members',
                'posts as open_needs_count' => function ($query): void {
                    $query->where('type', Post::TYPE_NEED)
                        ->whereNull('resolved_at')
                        ->whereNull('hidden_at')
                        ->where(function ($expires): void {
                            $expires->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        });
                },
            ])
            ->get();

        $pendingJoinCounts = CommunityJoinRequest::query()
            ->selectRaw('community_id, count(*) as aggregate')
            ->whereIn('community_id', $communities->pluck('id'))
            ->where('status', CommunityJoinRequest::STATUS_PENDING)
            ->groupBy('community_id')
            ->pluck('aggregate', 'community_id');

        $administered = $communities
            ->filter(fn (Community $community): bool => in_array(
                $community->pivot->role,
                [Community::ROLE_OWNER, Community::ROLE_MODERATOR],
                true,
            ))
            ->sortByDesc(fn (Community $community): array => [
                (int) ($pendingJoinCounts[$community->id] ?? 0),
                (int) $community->open_needs_count,
            ])
            ->values()
            ->map(fn (Community $community): array => $this->membershipCard(
                $community,
                pendingJoinRequests: (int) ($pendingJoinCounts[$community->id] ?? 0),
            ));

        $administeredIds = $administered->pluck('id')->all();

        $your = $communities
            ->reject(fn (Community $community): bool => in_array($community->id, $administeredIds, true))
            ->sortByDesc(function (Community $community): array {
                $pinnedAt = $community->pivot->pinned_at;

                return [
                    $pinnedAt !== null ? 1 : 0,
                    $pinnedAt !== null ? Carbon::parse($pinnedAt)->timestamp : 0,
                    (int) $community->open_needs_count,
                    $community->pivot->updated_at?->timestamp ?? 0,
                ];
            })
            ->values()
            ->map(fn (Community $community): array => $this->membershipCard($community));

        return [
            'your' => $your,
            'administered' => $administered,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, empty: bool}
     */
    private function nearCommunities(User $user): array
    {
        $items = $this->suggestions->for($user, 2)->values()->map(function (array $card) {
            return [
                ...$card,
                'monogram' => $this->monograms->for($card['name']),
                'join_href' => '/communities/'.$card['slug'],
            ];
        })->all();

        return [
            'items' => $items,
            'empty' => $items === [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstWeek(User $user, int $joinedCount, bool $hasHelpWith, bool $hasAskedOrAnswered): ?array
    {
        if ($user->home_checklist_dismissed_at !== null) {
            return null;
        }

        $steps = [
            [
                'key' => 'join',
                'label' => 'Join two communities',
                'done' => $joinedCount >= 2,
                'cta_label' => 'Find communities',
                'cta_href' => '/communities',
                'kind' => 'link',
            ],
            [
                'key' => 'help_with',
                'label' => 'Tell us what you can help with',
                'done' => $hasHelpWith,
                'cta_label' => 'Save',
                'cta_href' => null,
                'kind' => 'help_with',
            ],
            [
                'key' => 'ask_or_answer',
                'label' => 'Ask for help or answer a Need',
                'done' => $hasAskedOrAnswered,
                'cta_label' => 'Post a Need',
                'cta_href' => $this->postCreateHref($user, Post::TYPE_NEED),
                'kind' => 'link',
            ],
        ];

        $doneCount = collect($steps)->where('done', true)->count();

        if ($doneCount === count($steps)) {
            return null;
        }

        $activeIndex = collect($steps)->search(fn (array $step): bool => ! $step['done']);

        return [
            'done_count' => $doneCount,
            'total' => count($steps),
            'progress' => $doneCount / count($steps),
            'steps' => $steps,
            'active_key' => is_int($activeIndex) ? $steps[$activeIndex]['key'] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function membershipCard(Community $community, int $pendingJoinRequests = 0): array
    {
        $role = (string) $community->pivot->role;
        $openNeeds = (int) $community->open_needs_count;
        $members = (int) ($community->members_count ?? 0);
        $mono = $this->monograms->for($community->name);

        $summaryParts = [];
        if ($members > 0) {
            $summaryParts[] = $members.' '.($members === 1 ? 'member' : 'members');
        }
        if ($openNeeds > 0) {
            $summaryParts[] = $openNeeds.' open '.($openNeeds === 1 ? 'Need' : 'Needs');
        }
        if ($pendingJoinRequests > 0) {
            $summaryParts[] = $pendingJoinRequests.' join '.($pendingJoinRequests === 1 ? 'request' : 'requests');
        }

        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'location_label' => $community->location_label,
            'cover_url' => $community->coverUrl(),
            'tag_keys' => $community->cardTagKeys(),
            'monogram' => $mono,
            'role' => $role,
            'role_label' => match ($role) {
                Community::ROLE_OWNER => 'Owner',
                Community::ROLE_MODERATOR => 'Moderator',
                default => 'Member',
            },
            'pinned' => $community->pivot->pinned_at !== null,
            'open_needs_count' => $openNeeds,
            'members_count' => $members,
            'pending_join_requests' => $pendingJoinRequests,
            'summary' => $summaryParts !== [] ? implode(' · ', $summaryParts) : 'No open Needs right now',
            'href' => '/communities/'.$community->slug,
            'post_href' => '/communities/'.$community->slug.'/posts/create?type=need',
            'settings_href' => in_array($role, [Community::ROLE_OWNER, Community::ROLE_MODERATOR], true)
                ? '/communities/'.$community->slug.'/settings'
                : null,
            'pin_href' => '/communities/'.$community->slug.'/pin',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postCard(Post $post, bool $matched = false): array
    {
        $status = $post->isResolved()
            ? 'resolved'
            : ((int) $post->replies_count > 0 ? 'has_replies' : 'open');

        return [
            'id' => $post->id,
            'type' => $post->type,
            'type_label' => $post->typeLabel(),
            'title' => $post->title,
            'body' => $post->body,
            'category_key' => $post->category_key,
            'category_label' => $post->categoryLabel(),
            'location_label' => $post->location_label,
            'urgency' => $post->urgency,
            'urgency_label' => $post->urgencyLabel(),
            'status' => $status,
            'status_label' => match ($status) {
                'resolved' => 'Resolved',
                'has_replies' => 'Has replies',
                default => 'Open',
            },
            'replies_count' => (int) $post->replies_count,
            'created_at' => $post->created_at?->toIso8601String(),
            'href' => '/communities/'.$post->community->slug.'/posts/'.$post->id,
            'community' => [
                'slug' => $post->community->slug,
                'name' => $post->community->name,
            ],
            'match_reason' => $matched ? 'Matches what you can help with' : null,
        ];
    }

    private function categoryFilter(Request $request): ?string
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', Rule::in(array_keys(config('posts.categories', [])))],
        ]);

        $category = $validated['category'] ?? null;

        return is_string($category) && $category !== '' ? $category : null;
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function categoryFilterOptions(): array
    {
        return collect(config('posts.categories', []))
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'label' => $category['label'],
            ])
            ->values()
            ->all();
    }
}
