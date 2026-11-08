<?php

namespace App\Http\Controllers;

use App\Http\Requests\DismissHomeChecklistRequest;
use Illuminate\Http\RedirectResponse;

class HomeChecklistController extends Controller
{
    public function dismiss(DismissHomeChecklistRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'home_checklist_dismissed_at' => now(),
        ])->save();

        return back();
    }
}
