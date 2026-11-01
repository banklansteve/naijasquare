<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Show', [
            'profile' => [
                'name' => $user->name,
                'first_name' => $user->first_name,
                'email' => $user->email,
                'city' => $user->city,
                'place_label' => $user->place_label,
                'avatar_url' => $user->photo_path
                    ? route('profile.avatar')
                    : null,
            ],
        ]);
    }
}
