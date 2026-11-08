<?php

namespace App\Http\Controllers;

use App\Community\ModerateReportedContent;
use App\Http\Requests\ModerateCommunityReportRequest;
use App\Models\Community;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;

class CommunityReportController extends Controller
{
    public function dismiss(
        ModerateCommunityReportRequest $request,
        Community $community,
        Report $report,
        ModerateReportedContent $moderate,
    ): RedirectResponse {
        abort_unless((int) $report->community_id === (int) $community->id, 404);

        $moderate->dismiss($request->user(), $report);

        return back()->with('status', 'Report dismissed. The content stays up.');
    }

    public function remove(
        ModerateCommunityReportRequest $request,
        Community $community,
        Report $report,
        ModerateReportedContent $moderate,
    ): RedirectResponse {
        abort_unless((int) $report->community_id === (int) $community->id, 404);

        $moderate->remove($request->user(), $report);

        return back()->with('status', 'Content removed from the community.');
    }
}
