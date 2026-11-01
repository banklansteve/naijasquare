<?php

namespace App\Http\Controllers;

use App\Community\JoinCommunity;
use App\Http\Requests\JoinCommunitiesRequest;
use App\Models\Community;
use App\Signup\CommunitySuggestions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SuggestedCommunitiesController extends Controller
{
    public function create(Request $request, CommunitySuggestions $suggestions): Response|RedirectResponse
    {
        $communities = $suggestions->for($request->user());

        if ($communities->isEmpty()) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/Communities', [
            'communities' => $communities,
        ]);
    }

    public function store(
        JoinCommunitiesRequest $request,
        CommunitySuggestions $suggestions,
        JoinCommunity $join,
    ): RedirectResponse {
        $offered = $suggestions->idsFor($request->user());
        /** @var list<int> $chosen */
        $chosen = array_map('intval', $request->validated('communities'));

        if (array_diff($chosen, $offered) !== []) {
            throw ValidationException::withMessages([
                'communities' => 'Choose a community from the list, or skip for now.',
            ]);
        }

        foreach ($chosen as $communityId) {
            $community = Community::query()->findOrFail($communityId);

            if ($community->access_mode === Community::ACCESS_INVITE_ONLY) {
                continue;
            }

            $join->join($request->user(), $community);
        }

        return redirect()->route('home');
    }
}
