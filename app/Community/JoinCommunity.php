<?php

namespace App\Community;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class JoinCommunity
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function join(User $user, Community $community, ?string $note = null, bool $acceptGuidelines = false): string
    {
        if ($this->authorizer->isMember($user, $community)) {
            return 'already_member';
        }

        if ($community->access_mode === Community::ACCESS_INVITE_ONLY) {
            throw ValidationException::withMessages([
                'community' => 'This community is invite only.',
            ]);
        }

        if ($community->require_guidelines_accept && ! $acceptGuidelines) {
            throw ValidationException::withMessages([
                'accept_guidelines' => 'Please accept the community guidelines to continue.',
            ]);
        }

        if ($community->access_mode === Community::ACCESS_OPEN) {
            $community->members()->attach($user->id, [
                'role' => Community::ROLE_MEMBER,
                'guidelines_accepted_at' => $acceptGuidelines || ! $community->require_guidelines_accept ? now() : null,
            ]);

            return 'joined';
        }

        CommunityJoinRequest::query()->updateOrCreate(
            [
                'community_id' => $community->id,
                'user_id' => $user->id,
            ],
            [
                'note' => $note,
                'status' => CommunityJoinRequest::STATUS_PENDING,
            ],
        );

        return 'requested';
    }

    public function approve(User $actor, CommunityJoinRequest $request): void
    {
        $this->authorizer->authorize($actor, 'community.member.approve', $request->community);

        if ($request->status !== CommunityJoinRequest::STATUS_PENDING) {
            return;
        }

        $community = $request->community;
        $user = $request->user;

        if (! $this->authorizer->isMember($user, $community)) {
            $community->members()->attach($user->id, [
                'role' => Community::ROLE_MEMBER,
                'guidelines_accepted_at' => $community->require_guidelines_accept ? null : now(),
            ]);
        }

        $request->update(['status' => CommunityJoinRequest::STATUS_APPROVED]);
    }

    public function decline(User $actor, CommunityJoinRequest $request): void
    {
        $this->authorizer->authorize($actor, 'community.member.approve', $request->community);

        if ($request->status !== CommunityJoinRequest::STATUS_PENDING) {
            return;
        }

        $request->update(['status' => CommunityJoinRequest::STATUS_DECLINED]);
    }
}
