<?php

namespace App\Http\Controllers;

use App\Community\SuggestNeedDetails;
use App\Http\Requests\SuggestNeedDetailsRequest;
use App\Models\Community;
use Illuminate\Http\JsonResponse;

class NeedDetailsSuggestionController extends Controller
{
    public function __invoke(
        SuggestNeedDetailsRequest $request,
        Community $community,
        SuggestNeedDetails $suggest,
    ): JsonResponse {
        $data = $request->validated();

        return response()->json([
            'suggestion' => $suggest->for(
                $data['title'],
                $data['category_key'],
                $data['location_label'] ?? null,
                $community,
            ),
            'source' => 'template',
            'label' => 'Suggested details',
        ]);
    }
}
