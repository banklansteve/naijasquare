<?php

namespace App\Http\Controllers;

use App\Community\CommunityAuthorizer;
use App\Community\FindRelatedOffers;
use App\Models\Community;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RelatedOffersController extends Controller
{
    public function __invoke(
        Request $request,
        Community $community,
        CommunityAuthorizer $authorizer,
        FindRelatedOffers $relatedOffers,
    ): JsonResponse {
        $user = $request->user();
        abort_unless($authorizer->isMember($user, $community), 403);

        $validated = $request->validate([
            'category_key' => ['required', 'string', Rule::in(array_keys(config('posts.categories', [])))],
            'location_label' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json([
            'offers' => $relatedOffers->forNeed(
                $community,
                $validated['category_key'],
                $validated['location_label'] ?? null,
                $user,
            ),
        ]);
    }
}
