<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by('login:'.$request->ip()));
        RateLimiter::for('password-email', fn (Request $request) => Limit::perMinute(5)->by('password-email:'.$request->ip()));
        RateLimiter::for('password-update', fn (Request $request) => Limit::perMinute(5)->by('password-update:'.$request->ip()));
        RateLimiter::for('address-lookup', fn (Request $request) => Limit::perMinute(20)->by('address:'.$request->ip()));
        RateLimiter::for('verify-code', fn (Request $request) => Limit::perMinute(10)->by('verify-code:'.$request->user()?->id));
        RateLimiter::for('resend-code', fn (Request $request) => Limit::perMinute(6)->by('resend-code:'.$request->user()?->id));
        RateLimiter::for('community-create', fn (Request $request) => Limit::perMinute(5)->by('community-create:'.$request->user()?->id));
        RateLimiter::for('community-join', fn (Request $request) => Limit::perMinute(20)->by('community-join:'.$request->user()?->id));
        RateLimiter::for('user-search', fn (Request $request) => Limit::perMinute(30)->by('user-search:'.$request->user()?->id));

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });
    }
}
