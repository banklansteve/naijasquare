<?php

namespace App\Community;

use App\Models\Community;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageCommunity
{
    public function __construct(
        private CommunityAuthorizer $authorizer,
        private CommunityCovers $covers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGeneral(User $actor, Community $community, array $data, ?UploadedFile $cover = null): Community
    {
        $this->authorizer->authorize($actor, 'community.update', $community);

        if ($cover !== null) {
            $data['cover_path'] = $this->covers->store($cover, $community->id);
        }

        $community->update($data);

        return $community->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAccess(User $actor, Community $community, array $data): Community
    {
        $this->authorizer->authorize($actor, 'community.update', $community);
        $community->update($data);

        return $community->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGuidelines(User $actor, Community $community, array $data): Community
    {
        $this->authorizer->authorize($actor, 'community.guidelines.update', $community);
        $community->update($data);

        return $community->fresh();
    }

    public function removeModerator(User $actor, Community $community, User $moderator): void
    {
        $this->authorizer->authorize($actor, 'community.moderator.remove', $community);

        $role = $this->authorizer->roleIn($moderator, $community);

        if ($role !== Community::ROLE_MODERATOR) {
            throw ValidationException::withMessages([
                'user' => 'That person is not an admin of this community.',
            ]);
        }

        $community->members()->updateExistingPivot($moderator->id, [
            'role' => Community::ROLE_MEMBER,
        ]);
    }

    public function removeMember(User $actor, Community $community, User $member): void
    {
        $this->authorizer->authorize($actor, 'community.member.remove', $community);

        $role = $this->authorizer->roleIn($member, $community);

        if ($role === null) {
            return;
        }

        if ($role === Community::ROLE_OWNER) {
            throw ValidationException::withMessages([
                'user' => 'The creator cannot be removed. Transfer ownership first.',
            ]);
        }

        $community->members()->detach($member->id);
    }

    public function transferOwnership(User $actor, Community $community, User $newOwner): void
    {
        $this->authorizer->authorize($actor, 'community.transfer', $community);

        $newRole = $this->authorizer->roleIn($newOwner, $community);

        if ($newRole === null) {
            throw ValidationException::withMessages([
                'user_id' => 'The new creator must already be a member.',
            ]);
        }

        if ($newOwner->id === $actor->id) {
            throw ValidationException::withMessages([
                'user_id' => 'You are already the creator.',
            ]);
        }

        DB::transaction(function () use ($actor, $community, $newOwner): void {
            $community->members()->updateExistingPivot($newOwner->id, [
                'role' => Community::ROLE_OWNER,
            ]);
            $community->members()->updateExistingPivot($actor->id, [
                'role' => Community::ROLE_MODERATOR,
            ]);
            $community->update(['created_by' => $newOwner->id]);
        });
    }

    public function delete(User $actor, Community $community): void
    {
        $this->authorizer->authorize($actor, 'community.delete', $community);
        $community->delete();
    }

    public function acceptGuidelines(User $user, Community $community): void
    {
        abort_unless($this->authorizer->isMember($user, $community), 403);

        $community->members()->updateExistingPivot($user->id, [
            'guidelines_accepted_at' => now(),
        ]);
    }
}
