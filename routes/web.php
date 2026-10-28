<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LandingController::class, 'show'])->name('landing');

    Route::get('/join', [LandingController::class, 'join'])->name('register');
    Route::get('/sign-in', [LandingController::class, 'signIn'])->name('login');

    Route::get('/preview/invite', [LandingController::class, 'previewInvite'])->name('preview.invite');
});

Route::get('/{page}', [LandingController::class, 'page'])
    ->whereIn('page', ['privacy', 'guidelines', 'safety', 'contact'])
    ->name('info');
