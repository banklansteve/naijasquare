<?php

namespace App\Http\Controllers;

use App\Community\ReportContent;
use App\Http\Requests\StoreReportRequest;
use App\Models\Post;
use App\Models\PostReply;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, ReportContent $report): RedirectResponse
    {
        $data = $request->validated();

        $reportable = match ($data['reportable_type']) {
            'post' => Post::query()->findOrFail($data['reportable_id']),
            'reply' => PostReply::query()->findOrFail($data['reportable_id']),
        };

        $report->handle($request->user(), $reportable, [
            'reason_key' => $data['reason_key'],
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Thanks — we received your report.');
    }
}
