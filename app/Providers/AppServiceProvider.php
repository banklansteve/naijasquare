<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Sign-up, login, and password reset stay on our routes.
        // Fortify supplies the account contracts; its own routes are not used.
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('signup', fn (Request $request) => Limit::perMinute(5)->by('signup:'.$request->ip()));
        RateLimiter::for('address-lookup', fn (Request $request) => Limit::perMinute(20)->by('address:'.$request->ip()));
        RateLimiter::for('verify-code', fn (Request $request) => Limit::perMinute(10)->by('verify-code:'.$request->user()?->id));
        RateLimiter::for('resend-code', fn (Request $request) => Limit::perMinute(6)->by('resend-code:'.$request->user()?->id));
    }
}
