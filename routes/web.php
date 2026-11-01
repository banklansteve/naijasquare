<?php

use App\Http\Controllers\AdminInvitationController;
use App\Http\Controllers\AdminSearchController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\CommunitySettingsController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PostReplyController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuggestedCommunitiesController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/explore', [ExploreController::class, 'index'])->name('explore.index');
Route::get('/explore/tag/{tag}', [ExploreController::class, 'tag'])->name('explore.tag');
Route::get('/explore/{place}', [ExploreController::class, 'place'])->name('explore.place');

Route::middleware('guest')->group(function () {
    Route::get('/', [LandingController::class, 'show'])->name('landing');

    Route::get('/join', [SignupController::class, 'create'])->name('register');
    Route::post('/join/address', [SignupController::class, 'lookup'])->middleware('throttle:address-lookup');
    Route::post('/join', [SignupController::class, 'store'])->middleware('throttle:signup');

    Route::get('/sign-in', [LoginController::class, 'create'])->name('login');
    Route::post('/sign-in', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:password-email')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:password-update')->name('password.update');

    Route::get('/preview/invite', [LandingController::class, 'previewInvite'])->name('preview.invite');
});

Route::middleware('auth')->group(function () {
    Route::get('/join/verify', [EmailVerificationController::class, 'create'])->name('signup.verify');
    Route::post('/join/verify', [EmailVerificationController::class, 'store'])->middleware('throttle:verify-code');
    Route::post('/join/verify/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:resend-code')->name('signup.verify.resend');
    Route::post('/sign-out', LogoutController::class)->name('logout');

    Route::middleware('verified.email')->group(function () {
        Route::get('/home', HomeController::class)->name('home');
        Route::get('/needs', [GuideController::class, 'needs'])->name('needs');
        Route::get('/resources', [GuideController::class, 'resources'])->name('resources');
        Route::get('/profile', ProfileController::class)->name('profile');
        Route::get('/profile/avatar', ProfileAvatarController::class)->name('profile.avatar');
        Route::get('/join/communities', [SuggestedCommunitiesController::class, 'create'])->name('signup.communities');
        Route::post('/join/communities', [SuggestedCommunitiesController::class, 'store'])->name('signup.communities.join');

        Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
        Route::get('/communities/create', [CommunityController::class, 'create'])->name('communities.create');
        Route::post('/communities', [CommunityController::class, 'store'])->middleware('throttle:community-create')->name('communities.store');
        Route::post('/communities/{community}/join', [CommunityController::class, 'join'])->middleware('throttle:community-join')->name('communities.join');
        Route::post('/communities/{community}/accept-guidelines', [CommunityController::class, 'acceptGuidelines'])->name('communities.accept-guidelines');

        Route::get('/communities/{community}/posts/create', [CommunityPostController::class, 'create'])->name('communities.posts.create');
        Route::post('/communities/{community}/posts', [CommunityPostController::class, 'store'])->middleware('throttle:60,1')->name('communities.posts.store');
        Route::get('/communities/{community}/posts/{post}', [CommunityPostController::class, 'show'])->name('communities.posts.show');
        Route::post('/communities/{community}/posts/{post}/replies', [PostReplyController::class, 'store'])->middleware('throttle:60,1')->name('communities.posts.replies.store');
        Route::post('/communities/{community}/posts/{post}/resolve', [CommunityPostController::class, 'resolve'])->name('communities.posts.resolve');
        Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:20,1')->name('reports.store');

        Route::get('/communities/{community}/settings/{section?}', [CommunitySettingsController::class, 'show'])
            ->where('section', 'general|access|guidelines|admins|moderation|danger')
            ->name('communities.settings');
        Route::put('/communities/{community}/settings/general', [CommunitySettingsController::class, 'updateGeneral'])->name('communities.settings.general');
        Route::put('/communities/{community}/settings/access', [CommunitySettingsController::class, 'updateAccess'])->name('communities.settings.access');
        Route::put('/communities/{community}/settings/guidelines', [CommunitySettingsController::class, 'updateGuidelines'])->name('communities.settings.guidelines');
        Route::post('/communities/{community}/settings/admins/invite', [CommunitySettingsController::class, 'inviteAdmin'])->name('communities.settings.admins.invite');
        Route::delete('/communities/{community}/settings/admins/invites/{invitation}', [CommunitySettingsController::class, 'revokeInvite'])->name('communities.settings.admins.revoke');
        Route::delete('/communities/{community}/settings/admins/{user}', [CommunitySettingsController::class, 'removeModerator'])->name('communities.settings.admins.remove');
        Route::post('/communities/{community}/settings/join-requests/{joinRequest}/approve', [CommunitySettingsController::class, 'approveJoinRequest'])->name('communities.settings.join-requests.approve');
        Route::post('/communities/{community}/settings/join-requests/{joinRequest}/decline', [CommunitySettingsController::class, 'declineJoinRequest'])->name('communities.settings.join-requests.decline');
        Route::delete('/communities/{community}/settings/members/{user}', [CommunitySettingsController::class, 'removeMember'])->name('communities.settings.members.remove');
        Route::post('/communities/{community}/settings/transfer', [CommunitySettingsController::class, 'transfer'])->name('communities.settings.transfer');
        Route::delete('/communities/{community}', [CommunitySettingsController::class, 'destroy'])->name('communities.destroy');

        Route::get('/invitations/admin/{token}', [AdminInvitationController::class, 'show'])->name('invitations.admin.show');
        Route::post('/invitations/admin/{token}/accept', [AdminInvitationController::class, 'accept'])->name('invitations.admin.accept');
        Route::post('/invitations/admin/{token}/decline', [AdminInvitationController::class, 'decline'])->name('invitations.admin.decline');

        Route::get('/api/users/search', AdminSearchController::class)->middleware('throttle:user-search')->name('users.search');
    });
});

Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
Route::get('/communities/{community}/cover', [CommunityController::class, 'cover'])->name('communities.cover');

Route::get('/{page}', [LandingController::class, 'page'])
    ->whereIn('page', ['privacy', 'guidelines', 'safety', 'contact'])
    ->name('info');
