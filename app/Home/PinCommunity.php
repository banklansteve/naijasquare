<?php

namespace App\Home;

use App\Models\Community;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PinCommunity
{
    public function pin(User $user, Community $community): void
    {
        $this->assertMember($user, $community);

        $user->communities()->updateExistingPivot($community->id, [
            'pinned_at' => now(),
        ]);
    }

    public function unpin(User $user, Community $community): void
    {
        $this->assertMember($user, $community);

        $user->communities()->updateExistingPivot($community->id, [
            'pinned_at' => null,
        ]);
    }

    public function toggle(User $user, Community $community): bool
    {
        $this->assertMember($user, $community);

        $membership = $user->communities()
            ->where('communities.id', $community->id)
            ->first();

        $isPinned = $membership?->pivot?->pinned_at !== null;

        if ($isPinned) {
            $this->unpin($user, $community);

            return false;
        }

        $this->pin($user, $community);

        return true;
    }

    private function assertMember(User $user, Community $community): void
    {
        if (! $user->isMemberOf($community)) {
            throw ValidationException::withMessages([
                'community' => 'Join this community before you can pin it.',
            ]);
        }
    }
}
