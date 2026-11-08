<?php

namespace App\Community;

use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\Report;
use Illuminate\Support\Str;

class ListCommunityReports
{
    /**
     * @return list<array<string, mixed>>
     */
    public function pending(Community $community, int $limit = 50): array
    {
        return Report::query()
            ->with([
                'reporter:id,name',
                'reportable' => function ($morphTo): void {
                    $morphTo->morphWith([
                        Post::class => ['author:id,name'],
                        PostReply::class => ['author:id,name', 'post:id,community_id,title'],
                    ]);
                },
            ])
            ->where('community_id', $community->id)
            ->where('status', Report::STATUS_PENDING)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Report $report): ?array => $this->toCard($report, $community))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toCard(Report $report, Community $community): ?array
    {
        $reportable = $report->reportable;

        if ($reportable instanceof Post) {
            return [
                'id' => $report->id,
                'reason_key' => $report->reason_key,
                'reason_label' => (string) config("posts.report_reasons.{$report->reason_key}", $report->reason_key),
                'note' => $report->note,
                'created_at' => $report->created_at?->toIso8601String(),
                'reporter' => [
                    'id' => $report->reporter?->id,
                    'name' => $report->reporter?->name ?? 'Member',
                ],
                'target_type' => 'post',
                'target_label' => $reportable->typeLabel(),
                'title' => $reportable->title,
                'excerpt' => Str::limit($reportable->body, 160),
                'author_name' => $reportable->author?->name ?? 'Member',
                'href' => '/communities/'.$community->slug.'/posts/'.$reportable->id,
                'already_hidden' => $reportable->hidden_at !== null,
            ];
        }

        if ($reportable instanceof PostReply) {
            $post = $reportable->post;

            if ($post === null) {
                return null;
            }

            return [
                'id' => $report->id,
                'reason_key' => $report->reason_key,
                'reason_label' => (string) config("posts.report_reasons.{$report->reason_key}", $report->reason_key),
                'note' => $report->note,
                'created_at' => $report->created_at?->toIso8601String(),
                'reporter' => [
                    'id' => $report->reporter?->id,
                    'name' => $report->reporter?->name ?? 'Member',
                ],
                'target_type' => 'reply',
                'target_label' => 'Reply',
                'title' => 'Reply on “'.$post->title.'”',
                'excerpt' => Str::limit($reportable->body, 160),
                'author_name' => $reportable->author?->name ?? 'Member',
                'href' => '/communities/'.$community->slug.'/posts/'.$post->id,
                'already_hidden' => $reportable->hidden_at !== null,
            ];
        }

        return null;
    }
}
