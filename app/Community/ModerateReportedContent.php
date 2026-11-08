<?php

namespace App\Community;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModerateReportedContent
{
    public function __construct(private CommunityAuthorizer $authorizer) {}

    public function dismiss(User $moderator, Report $report): void
    {
        $this->assertCanModerate($moderator, $report);
        $this->assertPending($report);

        $report->forceFill([
            'status' => Report::STATUS_DISMISSED,
            'resolution_key' => Report::RESOLUTION_DISMISS,
            'reviewed_by_user_id' => $moderator->id,
            'reviewed_at' => now(),
        ])->save();
    }

    public function remove(User $moderator, Report $report): void
    {
        $this->assertCanModerate($moderator, $report);
        $this->assertPending($report);

        DB::transaction(function () use ($moderator, $report): void {
            $reportable = $report->reportable;

            if ($reportable instanceof Post || $reportable instanceof PostReply) {
                if ($reportable->hidden_at === null) {
                    $reportable->forceFill([
                        'hidden_at' => now(),
                        'hidden_by_user_id' => $moderator->id,
                    ])->save();
                }
            }

            Report::query()
                ->where('reportable_type', $report->reportable_type)
                ->where('reportable_id', $report->reportable_id)
                ->where('status', Report::STATUS_PENDING)
                ->update([
                    'status' => Report::STATUS_REMOVED,
                    'resolution_key' => Report::RESOLUTION_REMOVE,
                    'reviewed_by_user_id' => $moderator->id,
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    private function assertCanModerate(User $moderator, Report $report): void
    {
        $community = $report->community;

        if ($community === null || ! $this->authorizer->can($moderator, 'community.moderate', $community)) {
            abort(403);
        }
    }

    private function assertPending(Report $report): void
    {
        if ($report->status !== Report::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'report' => 'This report has already been reviewed.',
            ]);
        }
    }
}
