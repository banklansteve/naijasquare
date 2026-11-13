<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmCurrentPasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdateLocationDisplayRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdatePhoneRequest;
use App\Http\Requests\UpdatePrivacyPreferencesRequest;
use App\Http\Requests\UpdateProfilePhotoRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Profile\DeleteAccount;
use App\Profile\UpdateEmail;
use App\Profile\UpdateLocationDisplay;
use App\Profile\UpdatePassword;
use App\Profile\UpdatePhone;
use App\Profile\UpdatePrivacyPreferences;
use App\Profile\UpdateProfile;
use App\Profile\UpdateProfilePhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileSettingsController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request, UpdateProfile $update): RedirectResponse
    {
        $update->handle($request->user(), $request->validated());

        return back()->with('status', 'Profile updated.');
    }

    public function updatePrivacy(UpdatePrivacyPreferencesRequest $request, UpdatePrivacyPreferences $update): RedirectResponse
    {
        $update->handle($request->user(), $request->validated());

        return back()->with('status', 'Privacy and notification preferences saved.');
    }

    public function updateLocationDisplay(UpdateLocationDisplayRequest $request, UpdateLocationDisplay $update): RedirectResponse
    {
        $update->handle($request->user(), $request->validated('location_display'));

        return back()->with('status', 'Location visibility updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePassword $update): RedirectResponse
    {
        $data = $request->validated();
        $update->handle($request->user(), $data['current_password'], $data['password']);

        return back()->with('status', 'Password updated.');
    }

    public function confirmPassword(ConfirmCurrentPasswordRequest $request): JsonResponse
    {
        $matches = Hash::check(
            $request->validated('current_password'),
            (string) $request->user()->password,
        );

        if (! $matches) {
            throw ValidationException::withMessages([
                'current_password' => 'That password does not match our records.',
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function updateEmail(UpdateEmailRequest $request, UpdateEmail $update): RedirectResponse
    {
        $data = $request->validated();

        if (! Hash::check($data['current_password'], (string) $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'That password does not match our records.',
            ]);
        }

        $update->handle($request->user(), $data['email']);

        return redirect()
            ->route('signup.verify')
            ->with('status', 'Check your inbox for a new verification code.');
    }

    public function updatePhone(UpdatePhoneRequest $request, UpdatePhone $update): RedirectResponse
    {
        $update->handle($request->user(), $request->validated('phone'));

        return back()->with('status', 'Phone number saved.');
    }

    public function updatePhoto(UpdateProfilePhotoRequest $request, UpdateProfilePhoto $photos): RedirectResponse
    {
        $photos->store($request->user(), $request->file('photo'));

        return back()->with('status', 'Photo updated.');
    }

    public function destroyPhoto(UpdateProfilePhoto $photos): RedirectResponse
    {
        $photos->remove(request()->user());

        return back()->with('status', 'Photo removed.');
    }

    public function destroy(DeleteAccountRequest $request, DeleteAccount $delete): RedirectResponse
    {
        $data = $request->validated();

        if (! Hash::check($data['current_password'], (string) $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'That password does not match our records.',
            ]);
        }

        $user = $request->user();
        $delete->handle($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('landing')
            ->with('status', 'Your account has been deleted. We’re sorry to see you go.');
    }
}
