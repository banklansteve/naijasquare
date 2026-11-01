<?php

namespace App\Http\Controllers;

use App\Community\CommunityAuthorizer;
use App\Community\CreateCommunity;
use App\Community\JoinCommunity;
use App\Community\ManageCommunity;
use App\Community\PublicCommunityQuery;
use App\Http\Requests\JoinCommunityRequest;
use App\Http\Requests\StoreCommunityRequest;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\Post;
use App\Models\User;
use App\Signup\CommunitySuggestions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunityController extends Controller
{
    public function index(Request $request, CommunitySuggestions $suggestions): Response
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', Rule::in(array_keys(config('community.tags', [])))],
            'access_mode' => ['nullable', 'string', Rule::in(array_keys(config('community.access_modes', [])))],
            'sort' => ['nullable', 'string', Rule::in(['name', 'members', 'newest'])],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $tag = $filters['tag'] ?? null;
        $accessMode = $filters['access_mode'] ?? null;
        $sort = $filters['sort'] ?? 'members';
        $interests = $user->interests ?? [];

        $joinedQuery = $user->communities()->withCount('members');
        $this->applyBrowseFilters($joinedQuery, $q, $tag, $accessMode);
        $this->applyBrowseSort($joinedQuery, $sort);

        $joined = $joinedQuery
            ->get()
            ->map(fn (Community $community): array => $this->cardPayload($community, [
                'role' => $community->pivot->role,
                'members' => (int) $community->members_count,
            ]));

        $discoverQuery = $suggestions->discoverQuery($user);
        $this->applyBrowseFilters($discoverQuery, $q, $tag, $accessMode);
        $this->applyBrowseSort($discoverQuery, $sort);

        $discoverModels = $discoverQuery
            ->limit(80)
            ->get()
            ->filter(fn (Community $community): bool => $suggestions->matchesInterests($community, $interests));

        if ($sort === 'members') {
            $discoverModels = $discoverModels
                ->sortByDesc(fn (Community $community): array => [
                    count(array_intersect($community->cardTagKeys(), $interests)),
                    (int) $community->members_count,
                ])
                ->values();
        }

        $discover = $discoverModels
            ->take(48)
            ->map(fn (Community $community): array => $this->cardPayload($community, [
                'members' => (int) $community->members_count,
                'access_mode' => $community->access_mode,
            ]))
            ->values();

        return Inertia::render('Communities/Index', [
            'joined' => $joined,
            'discover' => $discover,
            'discoverHint' => $user->city
                ? 'Suggested for '.$user->city.' and your interests'
                : 'Add your city on your profile to get better local suggestions',
            'filters' => [
                'q' => $q,
                'tag' => $tag,
                'access_mode' => $accessMode,
                'sort' => $sort,
            ],
            'tags' => config('community.tags'),
            'accessModes' => config('community.access_modes'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Communities/Create', [
            'defaults' => [
                'location_type' => 'city',
                'location_label' => $request->user()->city,
                'city' => $request->user()->city,
                'access_mode' => Community::ACCESS_REQUEST,
                'who_can_post' => 'members',
            ],
            'tags' => config('community.tags'),
            'accessModes' => config('community.access_modes'),
            'whoCanPost' => config('community.who_can_post'),
            'locationTypes' => config('community.location_types'),
            'guidelineTemplates' => config('community.guideline_templates'),
        ]);
    }

    public function store(StoreCommunityRequest $request, CreateCommunity $create): RedirectResponse
    {
        $community = $create->handle(
            $request->user(),
            collect($request->validated())->except('cover')->all(),
            $request->file('cover'),
        );

        return redirect()
            ->route('communities.show', $community)
            ->with('status', 'Community created.');
    }

    public function show(
        Request $request,
        Community $community,
        CommunityAuthorizer $authorizer,
        PublicCommunityQuery $publicCommunities,
    ): Response {
        $user = $request->user();

        if ($user === null) {
            abort_unless($publicCommunities->isPubliclyVisible($community), 404);

            $card = $publicCommunities->toCard($community);
            $location = $card['location_label'] ?: 'the UK';
            $description = Str::limit(trim($community->description), 155) ?: "Join {$community->name}, a Nigerian diaspora community in {$location} on ".config('app.name').'.';

            $meta = [
                'title' => $community->name.($card['location_label'] ? ' · '.$card['location_label'] : ''),
                'description' => $description,
                'image' => $card['cover_url'] ?? config('landing.meta.image'),
            ];

            return Inertia::render('Communities/PublicShow', [
                'community' => $card,
                'authLinks' => [
                    'register' => route('register'),
                    'login' => route('login'),
                ],
            ])->withViewData(['meta' => $meta]);
        }

        $role = $authorizer->roleIn($user, $community);
        $isMember = $role !== null;

        $pendingRequest = null;
        if (! $isMember) {
            $pendingRequest = CommunityJoinRequest::query()
                ->where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->where('status', CommunityJoinRequest::STATUS_PENDING)
                ->first();
        }

        $guidelinesAccepted = false;
        if ($isMember) {
            $membership = $user->communities()->where('communities.id', $community->id)->first();
            $guidelinesAccepted = $membership?->pivot?->guidelines_accepted_at !== null;
        }

        $canManage = $authorizer->can($user, 'community.update', $community);

        $admins = $community->members()
            ->wherePivotIn('role', [Community::ROLE_OWNER, Community::ROLE_MODERATOR])
            ->get()
            ->sortBy(fn ($member): int => $member->pivot->role === Community::ROLE_OWNER ? 0 : 1)
            ->values()
            ->map(fn ($member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->pivot->role,
                'role_label' => $member->pivot->role === Community::ROLE_OWNER ? 'Creator' : 'Admin',
            ]);

        $tagLabels = collect($community->cardTagKeys())
            ->map(fn (string $key): ?string => config("community.tags.{$key}"))
            ->filter()
            ->values();

        $posts = $isMember
            ? $community->posts()
                ->with(['author:id,name'])
                ->withCount('replies')
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (Post $post): array => [
                    'id' => $post->id,
                    'type' => $post->type,
                    'type_label' => $post->typeLabel(),
                    'title' => $post->title,
                    'category_label' => $post->categoryLabel(),
                    'location_label' => $post->location_label,
                    'urgency_label' => $post->urgencyLabel(),
                    'is_resolved' => $post->isResolved(),
                    'is_expired' => $post->isExpired(),
                    'replies_count' => (int) $post->replies_count,
                    'author_name' => $post->author->name,
                    'created_at' => $post->created_at?->toIso8601String(),
                ])
            : collect();

        return Inertia::render('Communities/Show', [
            'community' => [
                'id' => $community->id,
                'slug' => $community->slug,
                'name' => $community->name,
                'description' => $community->description,
                'access_mode' => $community->access_mode,
                'access_label' => $community->accessModeLabel(),
                'location_label' => $community->location_label,
                'guidelines' => $community->guidelines,
                'require_guidelines_accept' => $community->require_guidelines_accept,
                'members_count' => $community->members()->count(),
                'tags' => $tagLabels,
                'cover_url' => $community->coverUrl(),
            ],
            'membership' => [
                'is_member' => $isMember,
                'role' => $role,
                'role_label' => match ($role) {
                    Community::ROLE_OWNER => 'Creator',
                    Community::ROLE_MODERATOR => 'Admin',
                    Community::ROLE_MEMBER => 'Member',
                    default => null,
                },
                'pending_request' => $pendingRequest !== null,
                'guidelines_accepted' => $guidelinesAccepted || ! $community->require_guidelines_accept,
                'can_manage' => $canManage,
                'can_post' => $isMember && $authorizer->canCreatePost($user, $community),
            ],
            'posts' => $posts,
            'admins' => $admins,
        ]);
    }

    public function join(
        JoinCommunityRequest $request,
        Community $community,
        JoinCommunity $join,
    ): RedirectResponse {
        $result = $join->join(
            $request->user(),
            $community,
            $request->validated('note'),
            (bool) $request->boolean('accept_guidelines'),
        );

        $message = match ($result) {
            'joined' => 'You joined the community.',
            'requested' => 'Your request to join was sent.',
            default => 'You are already a member.',
        };

        return back()->with('status', $message);
    }

    public function acceptGuidelines(Request $request, Community $community, ManageCommunity $manage): RedirectResponse
    {
        $manage->acceptGuidelines($request->user(), $community);

        return back()->with('status', 'Guidelines accepted.');
    }

    public function cover(Request $request, Community $community, PublicCommunityQuery $publicCommunities): StreamedResponse
    {
        abort_unless(is_string($community->cover_path) && $community->cover_path !== '', 404);
        abort_unless(Storage::disk('local')->exists($community->cover_path), 404);

        if ($request->user() === null) {
            abort_unless($publicCommunities->isPubliclyVisible($community), 404);
        }

        return Storage::disk('local')->response(
            $community->cover_path,
            headers: [
                'Cache-Control' => $request->user() === null
                    ? 'public, max-age=3600'
                    : 'private, max-age=3600',
            ],
        );
    }

    /**
     * @param  Builder<Community>|BelongsToMany<Community, User>  $query
     */
    private function applyBrowseFilters(mixed $query, string $q, ?string $tag, ?string $accessMode): void
    {
        $query
            ->when($q !== '', function ($builder) use ($q): void {
                $builder->where(function ($inner) use ($q): void {
                    $inner->where('communities.name', 'like', '%'.$q.'%')
                        ->orWhere('communities.description', 'like', '%'.$q.'%')
                        ->orWhere('communities.location_label', 'like', '%'.$q.'%')
                        ->orWhere('communities.city', 'like', '%'.$q.'%');
                });
            })
            ->when($tag !== null, function ($builder) use ($tag): void {
                $builder->where(function ($inner) use ($tag): void {
                    $inner->whereJsonContains('communities.tag_keys', $tag)
                        ->orWhereJsonContains('communities.interest_keys', $tag);
                });
            })
            ->when($accessMode !== null, fn ($builder) => $builder->where('communities.access_mode', $accessMode));
    }

    /**
     * @param  Builder<Community>|BelongsToMany<Community, User>  $query
     */
    private function applyBrowseSort(mixed $query, string $sort): void
    {
        match ($sort) {
            'name' => $query->orderBy('communities.name'),
            'newest' => $query->orderByDesc('communities.created_at'),
            default => $query->orderByDesc('members_count')->orderBy('communities.name'),
        };
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function cardPayload(Community $community, array $extra = []): array
    {
        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'description' => $community->description,
            'location_label' => $community->location_label,
            'cover_url' => $community->coverUrl(),
            'tag_keys' => $community->cardTagKeys(),
            ...$extra,
        ];
    }
}
