<?php

namespace App\Http\Controllers;

use App\Community\PublicCommunityQuery;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function index(PublicCommunityQuery $publicCommunities): Response
    {
        $places = $publicCommunities->places();
        $tags = $publicCommunities->tags();

        $meta = [
            'title' => 'Explore communities',
            'description' => 'Find Nigerian diaspora communities by city and interest across the UK. Join local groups for housing, work, and everyday help.',
            'image' => config('landing.meta.image'),
        ];

        return Inertia::render('Explore/Index', [
            'places' => $places,
            'tags' => $tags,
        ])->withViewData(['meta' => $meta]);
    }

    public function place(string $place, PublicCommunityQuery $publicCommunities): Response
    {
        $slug = Str::slug($place);
        $label = $publicCommunities->placeLabel($slug);

        abort_if($label === null, 404);

        $communities = $publicCommunities->forPlace($slug)
            ->orderByDesc('members_count')
            ->orderBy('name')
            ->get()
            ->map(fn ($community) => $publicCommunities->toCard($community))
            ->values();

        $meta = [
            'title' => "Communities in {$label}",
            'description' => "Discover Nigerian diaspora communities in {$label}. Find groups for housing, professionals, new arrivals, and local support on ".config('app.name').'.',
            'image' => config('landing.meta.image'),
        ];

        return Inertia::render('Explore/Place', [
            'place' => [
                'slug' => $slug,
                'label' => $label,
            ],
            'communities' => $communities,
        ])->withViewData(['meta' => $meta]);
    }

    public function tag(string $tag, PublicCommunityQuery $publicCommunities): Response
    {
        $tags = config('community.tags', []);

        abort_unless(array_key_exists($tag, $tags), 404);

        $label = $tags[$tag];

        $communities = $publicCommunities->forTag($tag)
            ->orderByDesc('members_count')
            ->orderBy('name')
            ->get()
            ->map(fn ($community) => $publicCommunities->toCard($community))
            ->values();

        abort_if($communities->isEmpty(), 404);

        $meta = [
            'title' => "{$label} communities",
            'description' => "Find {$label} communities for the Nigerian diaspora in the UK. Browse local groups and join on ".config('app.name').'.',
            'image' => config('landing.meta.image'),
        ];

        return Inertia::render('Explore/Tag', [
            'tag' => [
                'key' => $tag,
                'label' => $label,
            ],
            'communities' => $communities,
        ])->withViewData(['meta' => $meta]);
    }
}
