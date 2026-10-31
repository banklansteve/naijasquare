<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LookupAddressRequest;
use App\Http\Requests\RegisterAccountRequest;
use App\Signup\EmailVerificationCodes;
use App\Signup\PostcodeLookupFailed;
use App\Signup\PremiseDirectory;
use App\Signup\UnknownPostcode;
use App\Signup\UnknownPremise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SignupController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Signup', [
            'lifeStages' => config('signup.life_stages'),
            'interests' => config('signup.interests'),
            'privacyNote' => config('signup.privacy_note'),
            'inviteNote' => config('signup.invite_note'),
        ]);
    }

    public function lookup(LookupAddressRequest $request, PremiseDirectory $premises): JsonResponse
    {
        try {
            $confirmed = $premises->confirm(
                $request->validated('house_number'),
                $request->validated('postcode'),
            );
        } catch (UnknownPostcode) {
            throw ValidationException::withMessages([
                'postcode' => 'We could not find that postcode. Check it and try again.',
            ]);
        } catch (UnknownPremise $exception) {
            throw ValidationException::withMessages([
                'house_number' => $exception->getMessage(),
            ]);
        } catch (PostcodeLookupFailed) {
            throw ValidationException::withMessages([
                'postcode' => 'We could not look that up just now. Try again in a moment.',
            ]);
        }

        return response()->json($confirmed->lookup());
    }

    public function store(
        RegisterAccountRequest $request,
        CreateNewUser $creator,
        EmailVerificationCodes $codes,
    ): RedirectResponse {
        $user = $creator->create($request->validated());

        Auth::login($user);

        $codes->issue($user);

        return redirect()->route('signup.verify');
    }
}
