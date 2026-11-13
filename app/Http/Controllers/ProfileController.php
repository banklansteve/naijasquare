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

        $categories = collect(config('posts.categories', []))
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'label' => $category['label'],
            ])
            ->values()
            ->all();

        return Inertia::render('Profile/Show', [
            'profile' => [
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'city' => $user->city,
                'place_label' => $user->place_label,
                'postcode' => $user->postcode,
                'location_display' => $user->location_display ?: 'place',
                'display_location' => $user->displayLocation(),
                'profile_visibility' => $user->profile_visibility ?: 'communities',
                'help_visibility' => $user->help_visibility ?: 'communities',
                'avatar_url' => $user->photo_path
                    ? route('profile.avatar')
                    : null,
                'email_verified' => $user->email_verified_at !== null,
                'help_category_keys' => $user->help_category_keys ?? [],
                'help_note' => $user->help_note,
                'notify_needs_replies' => (bool) $user->notify_needs_replies,
                'notify_community' => (bool) $user->notify_community,
                'notify_admin' => (bool) $user->notify_admin,
                'prefer_product_emails' => (bool) $user->prefer_product_emails,
                'member_since' => $user->created_at?->toFormattedDateString(),
            ],
            'helpCategories' => $categories,
        ]);
    }
}
