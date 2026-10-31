<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinCommunitiesRequest;
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

    public function store(JoinCommunitiesRequest $request, CommunitySuggestions $suggestions): RedirectResponse
    {
        $offered = $suggestions->idsFor($request->user());
        /** @var list<int> $chosen */
        $chosen = array_map('intval', $request->validated('communities'));

        if (array_diff($chosen, $offered) !== []) {
            throw ValidationException::withMessages([
                'communities' => 'Choose a community from the list, or skip for now.',
            ]);
        }

        $request->user()->communities()->syncWithoutDetaching($chosen);

        return redirect()->route('home');
    }
}
