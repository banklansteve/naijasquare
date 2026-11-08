<?php

namespace App\Http\Middleware;

use App\Support\IntendedUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->email_verified_at === null) {
            IntendedUrl::rememberCurrentIfMissing($request);

            return redirect()->route('signup.verify');
        }

        return $next($request);
    }
}
