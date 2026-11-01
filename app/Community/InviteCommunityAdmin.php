<?php

namespace App\Community;

use App\Mail\CommunityAdminInvite;
use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityAdminInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InviteCommunityAdmin
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function invite(User $actor, Community $community, string $email): CommunityAdminInvitation
    {
        $this->authorizer->authorize($actor, 'community.moderator.invite', $community);

        $email = strtolower(trim($email));

        if ($email === strtolower($actor->email)) {
            throw ValidationException::withMessages([
                'email' => 'You already administer this community.',
            ]);
        }

        $invitee = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($invitee !== null) {
            $role = $this->authorizer->roleIn($invitee, $community);

            if (in_array($role, [Community::ROLE_OWNER, Community::ROLE_MODERATOR], true)) {
                throw ValidationException::withMessages([
                    'email' => 'That person is already an admin.',
                ]);
            }
        }

        $pending = CommunityAdminInvitation::query()
            ->where('community_id', $community->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', CommunityAdminInvitation::STATUS_PENDING)
            ->first();

        if ($pending !== null) {
            throw ValidationException::withMessages([
                'email' => 'An invitation is already pending for that email.',
            ]);
        }

        $invitation = CommunityAdminInvitation::query()->create([
            'community_id' => $community->id,
            'inviter_id' => $actor->id,
            'invitee_user_id' => $invitee?->id,
            'email' => $email,
            'status' => CommunityAdminInvitation::STATUS_PENDING,
        ]);

        Mail::to($email)->send(new CommunityAdminInvite($invitation));

        if ($invitee !== null) {
            AppNotification::query()->create([
                'user_id' => $invitee->id,
                'type' => 'community_admin_invite',
                'data' => [
                    'community_id' => $community->id,
                    'community_name' => $community->name,
                    'invitation_id' => $invitation->id,
                    'token' => $invitation->token,
                    'inviter_name' => $actor->name,
                ],
            ]);
        }

        return $invitation;
    }

    public function accept(User $user, CommunityAdminInvitation $invitation): Community
    {
        abort_unless($invitation->isPending(), 404);
        abort_unless(strtolower($user->email) === strtolower($invitation->email), 403);

        $community = $invitation->community;
        $role = $this->authorizer->roleIn($user, $community);

        if ($role === Community::ROLE_OWNER) {
            $invitation->update(['status' => CommunityAdminInvitation::STATUS_ACCEPTED]);

            return $community;
        }

        if ($role === null) {
            $community->members()->attach($user->id, [
                'role' => Community::ROLE_MODERATOR,
                'guidelines_accepted_at' => now(),
            ]);
        } else {
            $community->members()->updateExistingPivot($user->id, [
                'role' => Community::ROLE_MODERATOR,
            ]);
        }

        $invitation->update([
            'status' => CommunityAdminInvitation::STATUS_ACCEPTED,
            'invitee_user_id' => $user->id,
        ]);

        return $community;
    }

    public function decline(User $user, CommunityAdminInvitation $invitation): void
    {
        abort_unless($invitation->isPending(), 404);
        abort_unless(strtolower($user->email) === strtolower($invitation->email), 403);

        $invitation->update(['status' => CommunityAdminInvitation::STATUS_DECLINED]);
    }

    public function revoke(User $actor, CommunityAdminInvitation $invitation): void
    {
        $this->authorizer->authorize($actor, 'community.moderator.invite', $invitation->community);

        if ($invitation->isPending()) {
            $invitation->update(['status' => CommunityAdminInvitation::STATUS_REVOKED]);
        }
    }
}
