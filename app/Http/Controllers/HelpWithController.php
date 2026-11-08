<?php

namespace App\Http\Controllers;

use App\Home\UpdateHelpWith;
use App\Http\Requests\UpdateHelpWithRequest;
use Illuminate\Http\RedirectResponse;

class HelpWithController extends Controller
{
    public function update(UpdateHelpWithRequest $request, UpdateHelpWith $update): RedirectResponse
    {
        $data = $request->validated();

        $update->handle(
            $request->user(),
            $data['help_category_keys'],
            $data['help_note'] ?? null,
        );

        return back()->with('status', 'Thanks — we’ll use this to show Needs you can help with.');
    }
}
