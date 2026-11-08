<?php

namespace App\Community;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ReportContent
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    /**
     * @param  array{reason_key: string, note?: ?string}  $data
     */
    public function handle(User $reporter, Model $reportable, array $data): Report
    {
        $community = match (true) {
            $reportable instanceof Post => $reportable->community,
            $reportable instanceof PostReply => $reportable->post->community,
            default => null,
        };

        if ($community === null || ! $this->authorizer->isMember($reporter, $community)) {
            throw ValidationException::withMessages([
                'report' => 'You can only report content in communities you belong to.',
            ]);
        }

        $ownerId = match (true) {
            $reportable instanceof Post => (int) $reportable->user_id,
            $reportable instanceof PostReply => (int) $reportable->user_id,
            default => null,
        };

        if ($ownerId !== null && $ownerId === (int) $reporter->id) {
            throw ValidationException::withMessages([
                'report' => 'You cannot report your own post or reply.',
            ]);
        }

        $existing = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reportable_type', $reportable::class)
            ->where('reportable_id', $reportable->getKey())
            ->where('status', Report::STATUS_PENDING)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'report' => 'You already reported this. Thank you — we will review it.',
            ]);
        }

        return Report::query()->create([
            'community_id' => $community->id,
            'reporter_id' => $reporter->id,
            'reportable_type' => $reportable::class,
            'reportable_id' => $reportable->getKey(),
            'reason_key' => $data['reason_key'],
            'note' => $data['note'] ?? null,
            'status' => Report::STATUS_PENDING,
        ]);
    }
}
