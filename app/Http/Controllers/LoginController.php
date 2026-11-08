<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Support\IntendedUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(Request $request): Response
    {
        IntendedUrl::captureFromRequest($request);

        $redirect = $request->session()->get('url.intended');

        return Inertia::render('Auth/Login', [
            'redirect' => is_string($redirect) ? $redirect : null,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those details do not match an account.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->user()?->email_verified_at === null) {
            return redirect()->route('signup.verify');
        }

        return redirect()->intended(route('home'));
    }
}
