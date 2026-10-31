<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyEmailCodeRequest;
use App\Signup\EmailVerificationCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->email_verified_at !== null) {
            return redirect()->route('signup.communities');
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
        ]);
    }

    public function store(VerifyEmailCodeRequest $request, EmailVerificationCodes $codes): RedirectResponse
    {
        $codes->verify($request->user(), $request->validated('code'));

        return redirect()->route('signup.communities');
    }

    public function resend(Request $request, EmailVerificationCodes $codes): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null || $user->email_verified_at !== null, 404);

        $codes->issue($user);

        return back()->with('status', 'A new code is on its way.');
    }
}
