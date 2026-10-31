<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    public function needs(): Response
    {
        return Inertia::render('Guide', [
            'title' => 'Ask for help',
            'body' => 'A Need is a request for housing, work, advice, or a service. Replies stay on that post. Writing a Need opens with the next part of the app.',
        ]);
    }

    public function resources(): Response
    {
        return Inertia::render('Guide', [
            'title' => 'Explore local resources',
            'body' => 'Trusted recommendations near you will be listed here. That list opens with the next part of the app.',
        ]);
    }
}
