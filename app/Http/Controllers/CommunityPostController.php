<?php

namespace App\Http\Controllers;

use App\Community\CommunityAuthorizer;
use App\Community\CreatePost;
use App\Community\ResolvePost;
use App\Http\Requests\StorePostRequest;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CommunityPostController extends Controller
{
    public function create(Request $request, Community $community, CommunityAuthorizer $authorizer): Response
    {
        $user = $request->user();
        abort_unless($authorizer->isMember($user, $community), 403);
        abort_unless($authorizer->canCreatePost($user, $community), 403);

        $type = $request->validate([
            'type' => ['nullable', Rule::in([Post::TYPE_NEED, Post::TYPE_OFFER])],
        ])['type'] ?? Post::TYPE_NEED;

        return Inertia::render('Communities/Posts/Create', [
            'community' => [
                'id' => $community->id,
                'slug' => $community->slug,
                'name' => $community->name,
                'location_label' => $community->location_label,
            ],
            'type' => $type,
            'categories' => $this->categoryOptions(),
            'urgencies' => $this->urgencyOptions(),
            'safetyNotice' => config('posts.safety_notice'),
            'defaults' => [
                'location_label' => $community->location_label ?? $user->city,
                'urgency' => 'normal',
            ],
        ]);
    }

    public function store(
        StorePostRequest $request,
        Community $community,
        CreatePost $create,
    ): RedirectResponse {
        $post = $create->handle($request->user(), $community, $request->validated());

        return redirect()
            ->route('communities.posts.show', [$community, $post])
            ->with('status', $post->type === Post::TYPE_NEED ? 'Need posted.' : 'Offer posted.');
    }

    public function show(
        Request $request,
        Community $community,
        Post $post,
        CommunityAuthorizer $authorizer,
    ): Response {
        abort_unless($post->community_id === $community->id, 404);

        $user = $request->user();
        abort_unless($authorizer->isMember($user, $community), 403);

        $post->load(['author:id,name', 'replies.author:id,name']);

        return Inertia::render('Communities/Posts/Show', [
            'community' => [
                'id' => $community->id,
                'slug' => $community->slug,
                'name' => $community->name,
            ],
            'post' => $this->postPayload($post),
            'replies' => $post->replies
                ->sortBy('created_at')
                ->values()
                ->map(fn ($reply): array => [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'created_at' => $reply->created_at?->toIso8601String(),
                    'author' => [
                        'id' => $reply->author->id,
                        'name' => $reply->author->name,
                    ],
                ]),
            'permissions' => [
                'can_reply' => $authorizer->canReply($user, $community),
                'can_resolve' => $authorizer->canResolve($user, $post),
            ],
            'reportReasons' => config('posts.report_reasons'),
            'safetyNotice' => config('posts.safety_notice'),
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
            'expires_at' => $post->expires_at?->toIso8601String(),
            'resolved_at' => $post->resolved_at?->toIso8601String(),
            'is_expired' => $post->isExpired(),
            'is_resolved' => $post->isResolved(),
            'requires_safety_warning' => $post->requiresSafetyWarning(),
            'created_at' => $post->created_at?->toIso8601String(),
            'author' => [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ],
            'replies_count' => $post->replies->count(),
        ];
    }
}
