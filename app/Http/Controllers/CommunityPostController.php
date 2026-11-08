<?php

namespace App\Http\Controllers;

use App\Community\CommunityAuthorizer;
use App\Community\CreatePost;
use App\Community\FindRelatedOffers;
use App\Community\NotifyMatchingOfferAuthors;
use App\Community\PostFormHints;
use App\Community\ResolvePost;
use App\Http\Requests\StorePostRequest;
use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Support\HumanTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CommunityPostController extends Controller
{
    public function create(
        Request $request,
        Community $community,
        CommunityAuthorizer $authorizer,
        PostFormHints $hints,
        FindRelatedOffers $relatedOffers,
    ): Response {
        $user = $request->user();
        abort_unless($authorizer->isMember($user, $community), 403);
        abort_unless($authorizer->canCreatePost($user, $community), 403);

        $type = $request->validate([
            'type' => ['nullable', Rule::in([Post::TYPE_NEED, Post::TYPE_OFFER])],
        ])['type'] ?? Post::TYPE_NEED;

        $city = is_string($user->city) && $user->city !== '' ? $user->city : null;
        $formHints = $hints->for($community, $type, null, $city);
        $defaultCategory = $formHints['suggested_category_key'];
        $defaultLocation = $city ?? $community->city ?? $community->location_label;

        return Inertia::render('Communities/Posts/Create', [
            'community' => [
                'id' => $community->id,
                'slug' => $community->slug,
                'name' => $community->name,
                'city' => $community->city,
                'location_label' => $community->location_label,
            ],
            'type' => $type,
            'categories' => $this->categoryOptions(),
            'urgencies' => $this->urgencyOptions(),
            'safetyNotice' => config('posts.safety_notice'),
            'defaults' => [
                'location_label' => $defaultLocation,
                'urgency' => 'normal',
                'category_key' => $defaultCategory,
            ],
            'userCity' => $city,
            'hints' => $formHints,
            'locationSuggestions' => $hints->locationSuggestions($community, $city),
            'relatedOffers' => $type === Post::TYPE_NEED
                ? $relatedOffers->forNeed(
                    $community,
                    $defaultCategory,
                    is_string($defaultLocation) ? $defaultLocation : null,
                    $user,
                )
                : [],
        ]);
    }

    public function store(
        StorePostRequest $request,
        Community $community,
        CreatePost $create,
        NotifyMatchingOfferAuthors $notifyOfferAuthors,
    ): RedirectResponse {
        $post = $create->handle($request->user(), $community, $request->validated());

        if ($post->type === Post::TYPE_NEED) {
            $notifyOfferAuthors->handle($post);
        }

        return redirect()
            ->route('communities.posts.show', [$community, $post])
            ->with('status', $post->type === Post::TYPE_NEED ? 'Need posted.' : 'Offer posted.');
    }

    public function show(
        Request $request,
        Community $community,
        Post $post,
        CommunityAuthorizer $authorizer,
        FindRelatedOffers $relatedOffers,
    ): Response {
        abort_unless($post->community_id === $community->id, 404);

        $user = $request->user();
        abort_unless($authorizer->isMember($user, $community), 403);

        $canModerate = $authorizer->can($user, 'community.moderate', $community);

        if ($post->isHidden() && ! $canModerate) {
            return Inertia::render('Communities/Posts/Removed', [
                'community' => [
                    'slug' => $community->slug,
                    'name' => $community->name,
                ],
            ]);
        }

        $post->load(['author:id,name', 'replies' => function ($query) use ($canModerate): void {
            $query->with('author:id,name');
            if (! $canModerate) {
                $query->whereNull('hidden_at');
            }
        }]);

        return Inertia::render('Communities/Posts/Show', [
            'community' => [
                'id' => $community->id,
                'slug' => $community->slug,
                'name' => $community->name,
            ],
            'post' => $this->postPayload($post),
            'replies' => $post->replies
                ->sortBy([
                    fn (PostReply $reply): int => $reply->isHelped() ? 0 : 1,
                    fn (PostReply $reply): int => $reply->created_at?->getTimestamp() ?? 0,
                ])
                ->values()
                ->map(fn (PostReply $reply): array => [
                    'id' => $reply->id,
                    'body' => $reply->isHidden() ? '' : $reply->body,
                    'is_hidden' => $reply->isHidden(),
                    'is_helped' => $reply->isHelped(),
                    'helped_at' => $reply->helped_at?->toIso8601String(),
                    'created_at' => $reply->created_at?->toIso8601String(),
                    'created_at_human' => HumanTime::format($reply->created_at),
                    'author' => [
                        'id' => $reply->author->id,
                        'name' => $reply->author->name,
                    ],
                ]),
            'permissions' => [
                'can_reply' => $authorizer->canReply($user, $community)
                    && ! $post->isHidden()
                    && (int) $post->user_id !== (int) $user->id,
                'can_report' => (int) $post->user_id !== (int) $user->id && ! $post->isHidden(),
                'can_mark_helped' => $post->isNeed()
                    && (int) $post->user_id === (int) $user->id
                    && ! $post->isHidden(),
                'can_resolve' => $authorizer->canResolve($user, $post) && ! $post->isHidden(),
                'can_moderate' => $canModerate,
                'is_author' => (int) $post->user_id === (int) $user->id,
            ],
            'reportReasons' => config('posts.report_reasons'),
            'safetyNotice' => config('posts.safety_notice'),
            'isHidden' => $post->isHidden(),
            'relatedOffers' => $post->type === Post::TYPE_NEED && ! $post->isHidden()
                ? $relatedOffers->forNeed(
                    $community,
                    $post->category_key,
                    $post->locationLabels()[0] ?? $post->location_label,
                    $user,
                )
                : [],
        ]);
    }

    public function resolve(
        Request $request,
        Community $community,
        Post $post,
        ResolvePost $resolve,
    ): RedirectResponse {
        abort_unless($post->community_id === $community->id, 404);

        $resolve->handle($request->user(), $post);

        return back()->with('status', 'Marked as resolved.');
    }

    /**
     * @return list<array{key: string, label: string, safety_warning: bool}>
     */
    private function categoryOptions(): array
    {
        return collect(config('posts.categories', []))
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'label' => $category['label'],
                'safety_warning' => (bool) ($category['safety_warning'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function urgencyOptions(): array
    {
        return collect(config('posts.urgencies', []))
            ->map(fn (array $urgency, string $key): array => [
                'key' => $key,
                'label' => $urgency['label'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function postPayload(Post $post): array
    {
        $locations = $post->locationLabels();

        return [
            'id' => $post->id,
            'type' => $post->type,
            'type_label' => $post->typeLabel(),
            'title' => $post->title,
            'body' => $post->body,
            'category_key' => $post->category_key,
            'category_keys' => $post->categoryKeys(),
            'category_label' => $post->categoryLabel(),
            'category_labels' => $post->categoryLabels(),
            'location_label' => $locations[0] ?? null,
            'location_labels' => $locations,
            'location_specific' => $locations !== [],
            'urgency' => $post->urgency,
            'urgency_label' => $post->isOffer() ? null : $post->urgencyLabel(),
            'expires_at' => $post->expires_at?->toIso8601String(),
            'resolved_at' => $post->resolved_at?->toIso8601String(),
            'is_expired' => $post->isExpired(),
            'is_resolved' => $post->isResolved(),
            'requires_safety_warning' => $post->requiresSafetyWarning(),
            'created_at' => $post->created_at?->toIso8601String(),
            'created_at_human' => HumanTime::format($post->created_at),
            'author' => [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ],
            'replies_count' => $post->replies->count(),
        ];
    }
}
