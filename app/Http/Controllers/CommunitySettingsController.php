<?php

namespace App\Http\Controllers;

use App\Community\CommunityAuthorizer;
use App\Community\InviteCommunityAdmin;
use App\Community\JoinCommunity;
use App\Community\ManageCommunity;
use App\Http\Requests\DeleteCommunityRequest;
use App\Http\Requests\InviteCommunityAdminRequest;
use App\Http\Requests\TransferCommunityRequest;
use App\Http\Requests\UpdateCommunityAccessRequest;
use App\Http\Requests\UpdateCommunityGeneralRequest;
use App\Http\Requests\UpdateCommunityGuidelinesRequest;
use App\Models\Community;
use App\Models\CommunityAdminInvitation;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CommunitySettingsController extends Controller
{
    public function show(Request $request, Community $community, CommunityAuthorizer $authorizer, ?string $section = 'general'): Response
    {
        $user = $request->user();
        $authorizer->authorize($user, 'community.update', $community);

        $section ??= 'general';
        $allowed = ['general', 'access', 'guidelines', 'admins', 'moderation', 'danger'];
        abort_unless(in_array($section, $allowed, true), 404);

        return Inertia::render('Communities/Settings', [
            'section' => $section,
            'community' => $this->communityPayload($community),
            'config' => [
                'tags' => config('community.tags'),
                'accessModes' => config('community.access_modes'),
                'locationTypes' => config('community.location_types'),
                'guidelineTemplates' => config('community.guideline_templates'),
                'whoCanPost' => config('community.who_can_post'),
                'memberListVisibility' => config('community.member_list_visibility'),
            ],
            'permissions' => [
                'can_delete' => $authorizer->can($user, 'community.delete', $community),
                'can_transfer' => $authorizer->can($user, 'community.transfer', $community),
                'can_invite_admin' => $authorizer->can($user, 'community.moderator.invite', $community),
                'can_remove_admin' => $authorizer->can($user, 'community.moderator.remove', $community),
                'can_approve' => $authorizer->can($user, 'community.member.approve', $community),
                'can_remove_member' => $authorizer->can($user, 'community.member.remove', $community),
            ],
            'admins' => $community->members()
                ->wherePivotIn('role', [Community::ROLE_OWNER, Community::ROLE_MODERATOR])
                ->get()
                ->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role,
                    'role_label' => $member->pivot->role === Community::ROLE_OWNER ? 'Creator' : 'Admin',
                ]),
            'pendingInvites' => $community->adminInvitations()
                ->where('status', CommunityAdminInvitation::STATUS_PENDING)
                ->latest()
                ->get()
                ->map(fn (CommunityAdminInvitation $invite): array => [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'created_at' => $invite->created_at?->toIso8601String(),
                ]),
            'joinRequests' => $community->joinRequests()
                ->where('status', CommunityJoinRequest::STATUS_PENDING)
                ->with('user:id,name,email')
                ->latest()
                ->get()
                ->map(fn (CommunityJoinRequest $joinRequest): array => [
                    'id' => $joinRequest->id,
                    'note' => $joinRequest->note,
                    'created_at' => $joinRequest->created_at?->toIso8601String(),
                    'user' => [
                        'id' => $joinRequest->user->id,
                        'name' => $joinRequest->user->name,
                        'email' => $joinRequest->user->email,
                    ],
                ]),
            'members' => $community->members()
                ->wherePivot('role', Community::ROLE_MEMBER)
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                ]),
            'transferCandidates' => $community->members()
                ->where('users.id', '!=', $user->id)
                ->orderBy('name')
                ->get()
                ->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->pivot->role,
                ]),
        ]);
    }

    public function updateGeneral(
        UpdateCommunityGeneralRequest $request,
        Community $community,
        ManageCommunity $manage,
    ): RedirectResponse {
        $data = collect($request->validated())->except(['cover'])->all();
        $manage->updateGeneral($request->user(), $community, $data, $request->file('cover'));

        return back()->with('status', 'General settings saved.');
    }

    public function updateAccess(
        UpdateCommunityAccessRequest $request,
        Community $community,
        ManageCommunity $manage,
    ): RedirectResponse {
        $manage->updateAccess($request->user(), $community, $request->validated());

        return back()->with('status', 'Access settings saved.');
    }

    public function updateGuidelines(
        UpdateCommunityGuidelinesRequest $request,
        Community $community,
        ManageCommunity $manage,
    ): RedirectResponse {
        $manage->updateGuidelines($request->user(), $community, $request->validated());

        return back()->with('status', 'Guidelines saved.');
    }

    public function inviteAdmin(
        InviteCommunityAdminRequest $request,
        Community $community,
        InviteCommunityAdmin $invite,
    ): RedirectResponse {
        $invite->invite($request->user(), $community, $request->validated('email'));

        return back()->with('status', 'Admin invitation sent.');
    }

    public function revokeInvite(
        Request $request,
        Community $community,
        CommunityAdminInvitation $invitation,
        InviteCommunityAdmin $invite,
    ): RedirectResponse {
        abort_unless($invitation->community_id === $community->id, 404);
        $invite->revoke($request->user(), $invitation);

        return back()->with('status', 'Invitation revoked.');
    }

    public function removeModerator(
        Request $request,
        Community $community,
        User $user,
        ManageCommunity $manage,
    ): RedirectResponse {
        $manage->removeModerator($request->user(), $community, $user);

        return back()->with('status', 'Admin removed.');
    }

    public function approveJoinRequest(
        Request $request,
        Community $community,
        CommunityJoinRequest $joinRequest,
        JoinCommunity $join,
    ): RedirectResponse {
        abort_unless($joinRequest->community_id === $community->id, 404);
        $join->approve($request->user(), $joinRequest);

        return back()->with('status', 'Join request approved.');
    }

    public function declineJoinRequest(
        Request $request,
        Community $community,
        CommunityJoinRequest $joinRequest,
        JoinCommunity $join,
    ): RedirectResponse {
        abort_unless($joinRequest->community_id === $community->id, 404);
        $join->decline($request->user(), $joinRequest);

        return back()->with('status', 'Join request declined.');
    }

    public function removeMember(
        Request $request,
        Community $community,
        User $user,
        ManageCommunity $manage,
    ): RedirectResponse {
        $manage->removeMember($request->user(), $community, $user);

        return back()->with('status', 'Member removed.');
    }

    public function transfer(
        TransferCommunityRequest $request,
        Community $community,
        ManageCommunity $manage,
    ): RedirectResponse {
        $newOwner = User::query()->findOrFail($request->validated('user_id'));
        $manage->transferOwnership($request->user(), $community, $newOwner);

        return redirect()
            ->route('communities.show', $community)
            ->with('status', 'Ownership transferred.');
    }

    public function destroy(
        DeleteCommunityRequest $request,
        Community $community,
        ManageCommunity $manage,
    ): RedirectResponse {
        if ($request->validated('confirm_name') !== $community->name) {
            throw ValidationException::withMessages([
                'confirm_name' => 'Type the community name exactly to confirm.',
            ]);
        }

        $manage->delete($request->user(), $community);

        return redirect()
            ->route('communities.index')
            ->with('status', 'Community deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function communityPayload(Community $community): array
    {
        return [
            'id' => $community->id,
            'slug' => $community->slug,
            'name' => $community->name,
            'description' => $community->description,
            'access_mode' => $community->access_mode,
            'location_type' => $community->location_type,
            'location_label' => $community->location_label,
            'city' => $community->city,
            'tag_keys' => $community->tag_keys ?? [],
            'guidelines' => $community->guidelines,
            'require_guidelines_accept' => $community->require_guidelines_accept,
            'who_can_post' => $community->who_can_post,
            'member_list_visibility' => $community->member_list_visibility,
            'admin_digest_enabled' => $community->admin_digest_enabled,
            'cover_url' => $community->cover_path
                ? route('communities.cover', $community)
                : null,
        ];
    }
}
