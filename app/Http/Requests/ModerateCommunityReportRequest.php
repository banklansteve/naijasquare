<?php

namespace App\Http\Requests;

use App\Community\CommunityAuthorizer;
use App\Models\Community;
use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class ModerateCommunityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $community = $this->route('community');
        $report = $this->route('report');

        if (! $community instanceof Community || ! $report instanceof Report) {
            return false;
        }

        if ((int) $report->community_id !== (int) $community->id) {
            abort(404);
        }

        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return app(CommunityAuthorizer::class)->can($user, 'community.moderate', $community);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
