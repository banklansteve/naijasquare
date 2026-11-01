<?php

namespace App\Http\Controllers;

use App\Community\PublicCommunityQuery;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(PublicCommunityQuery $publicCommunities): Response
    {
        $urls = [
            [
                'loc' => route('landing'),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('explore.index'),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        foreach ($publicCommunities->places() as $place) {
            $urls[] = [
                'loc' => route('explore.place', $place['slug']),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        foreach ($publicCommunities->tags() as $tag) {
            $urls[] = [
                'loc' => route('explore.tag', $tag['key']),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        foreach ($publicCommunities->base()->orderBy('slug')->get(['slug', 'updated_at']) as $community) {
            $urls[] = [
                'loc' => route('communities.show', $community),
                'lastmod' => optional($community->updated_at)?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
