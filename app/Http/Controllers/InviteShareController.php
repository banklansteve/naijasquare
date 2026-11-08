<?php

namespace App\Http\Controllers;

use App\Home\PersonalInviteLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InviteShareController extends Controller
{
    /**
     * Rate-limited share payload for WhatsApp / copy actions.
     */
    public function __invoke(Request $request, PersonalInviteLink $invites): JsonResponse
    {
        return response()->json($invites->for($request->user()));
    }
}
