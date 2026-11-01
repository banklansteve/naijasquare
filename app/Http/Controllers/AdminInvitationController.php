<?php

namespace App\Http\Controllers;

use App\Community\InviteCommunityAdmin;
use App\Models\CommunityAdminInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminInvitationController extends Controller
{
    public function show(Request $request, string $token): Response|RedirectResponse
    {
        $invitation = CommunityAdminInvitation::query()
            ->where('token', $token)
            ->with(['community', 'inviter'])
            ->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()
                ->route('home')
                ->with('status', 'This invitation is no longer active.');
        }

        $user = $request->user();
        abort_unless(strtolower($user->email) === strtolower($invitation->email), 403);

        return Inertia::render('Invitations/Admin', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'community' => [
                    'name' => $invitation->community->name,
                    'slug' => $invitation->community->slug,
                    'description' => $invitation->community->description,
                ],
                'inviter' => [
                    'name' => $invitation->inviter->name,
                ],
            ],
        ]);
    }

    public function accept(Request $request, string $token, InviteCommunityAdmin $invite): RedirectResponse
    {
        $invitation = CommunityAdminInvitation::query()->where('token', $token)->firstOrFail();
        $community = $invite->accept($request->user(), $invitation);

        return redirect()
            ->route('communities.show', $community)
            ->with('status', 'You are now an admin of this community.');
    }

    public function decline(Request $request, string $token, InviteCommunityAdmin $invite): RedirectResponse
    {
        $invitation = CommunityAdminInvitation::query()->where('token', $token)->firstOrFail();
        $invite->decline($request->user(), $invitation);

        return redirect()
            ->route('home')
            ->with('status', 'Invitation declined.');
    }
}
