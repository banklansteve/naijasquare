<?php

namespace App\Community;

use App\Models\Community;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CommunityAuthorizer
{
    public function can(User $user, string $permission, Community $community): bool
    {
        $role = $this->roleIn($user, $community);

        if ($role === null) {
            return false;
        }

        return in_array($permission, $this->permissionsFor($role), true);
    }

    public function roleIn(User $user, Community $community): ?string
    {
        $membership = $user->communities()
            ->where('communities.id', $community->id)
            ->first();

        if ($membership === null) {
            return null;
        }

        $role = $membership->pivot->role ?? null;

        return is_string($role) && $role !== '' ? $role : 'member';
    }

    public function isMember(User $user, Community $community): bool
    {
        return $this->roleIn($user, $community) !== null;
    }

    public function hasAcceptedGuidelines(User $user, Community $community): bool
    {
        if (! $community->require_guidelines_accept) {
            return true;
        }

        $membership = $user->communities()
            ->where('communities.id', $community->id)
            ->first();

        return $membership?->pivot?->guidelines_accepted_at !== null;
    }

    public function canCreatePost(User $user, Community $community): bool
    {
        if (! $this->can($user, 'post.create', $community)) {
            return false;
        }

        if (! $this->hasAcceptedGuidelines($user, $community)) {
            return false;
        }

        if ($community->who_can_post === 'admins') {
            $role = $this->roleIn($user, $community);

            return in_array($role, [Community::ROLE_OWNER, Community::ROLE_MODERATOR], true);
        }

        return true;
    }

    public function canReply(User $user, Community $community): bool
    {
        return $this->can($user, 'post.reply', $community)
            && $this->hasAcceptedGuidelines($user, $community);
    }

    public function canResolve(User $user, Post $post): bool
    {
        if ($post->user_id !== $user->id) {
            return false;
        }

        return $this->can($user, 'post.resolve_own', $post->community);
    }

    /**
     * @return list<string>
     */
    public function permissionsFor(string $role): array
    {
        return Cache::remember("community.role_permissions.v2.{$role}", 3600, function () use ($role): array {
            return Permission::keysForRole($role);
        });
    }

    public function authorize(User $user, string $permission, Community $community): void
    {
        abort_unless($this->can($user, $permission, $community), 403);
    }
}
