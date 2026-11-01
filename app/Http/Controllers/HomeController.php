<?php

namespace App\Http\Controllers;

use App\Signup\CommunitySuggestions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, CommunitySuggestions $suggestions): Response
    {
        $user = $request->user();

        return Inertia::render('Home', [
            'firstName' => $user->first_name,
            'city' => $user->city,
            'communities' => $suggestions->for($user)->values(),
        ]);
    }
}
