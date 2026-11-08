<?php

namespace App\Http\Controllers;

use App\Home\PinCommunity;
use App\Models\Community;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityPinController extends Controller
{
    public function update(Request $request, Community $community, PinCommunity $pins): RedirectResponse
    {
        $pinned = $pins->toggle($request->user(), $community);

        return back()->with(
            'status',
            $pinned ? 'Pinned for quick access.' : 'Removed from pinned communities.',
        );
    }
}
