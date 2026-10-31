<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SuggestedCommunitiesController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LandingController::class, 'show'])->name('landing');

    Route::get('/join', [SignupController::class, 'create'])->name('register');
    Route::post('/join/address', [SignupController::class, 'lookup'])->middleware('throttle:address-lookup');
    Route::post('/join', [SignupController::class, 'store'])->middleware('throttle:signup');

    Route::get('/sign-in', [LandingController::class, 'signIn'])->name('login');

    Route::get('/preview/invite', [LandingController::class, 'previewInvite'])->name('preview.invite');
});

Route::middleware('auth')->group(function () {
    Route::get('/join/verify', [EmailVerificationController::class, 'create'])->name('signup.verify');
    Route::post('/join/verify', [EmailVerificationController::class, 'store'])->middleware('throttle:verify-code');
    Route::post('/join/verify/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:resend-code')->name('signup.verify.resend');

    Route::middleware('verified.email')->group(function () {
        Route::get('/home', HomeController::class)->name('home');
        Route::get('/needs', [GuideController::class, 'needs'])->name('needs');
        Route::get('/resources', [GuideController::class, 'resources'])->name('resources');
        Route::get('/join/communities', [SuggestedCommunitiesController::class, 'create'])->name('signup.communities');
        Route::post('/join/communities', [SuggestedCommunitiesController::class, 'store'])->name('signup.communities.join');
    });
});

Route::get('/{page}', [LandingController::class, 'page'])
    ->whereIn('page', ['privacy', 'guidelines', 'safety', 'contact'])
    ->name('info');
