<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Signup\ConfirmedAddress;
use App\Signup\PostcodeLookupFailed;
use App\Signup\PremiseDirectory;
use App\Signup\ProfilePhotos;
use App\Signup\UnknownPostcode;
use App\Signup\UnknownPremise;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(
        private PremiseDirectory $premises,
        private ProfilePhotos $photos,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $confirmed = $this->confirm($input);

        $user = User::query()->create([
            'name' => trim($input['first_name'].' '.$input['last_name']),
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'password' => $input['password'],
            'house_number' => $input['house_number'],
            'postcode' => $confirmed->place->postcode,
            'address_line' => $confirmed->addressLine,
            'place_label' => $confirmed->place->label(),
            'city' => $confirmed->place->city,
            'life_stage' => $input['life_stage'],
            'interests' => array_values($input['interests']),
            'terms_accepted_at' => now(),
        ]);

        $photo = $input['photo'] ?? null;

        if ($photo instanceof UploadedFile) {
            $user->forceFill([
                'photo_path' => $this->photos->store($photo, $user->id),
            ])->save();
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function confirm(array $input): ConfirmedAddress
    {
        try {
            return $this->premises->confirm((string) $input['house_number'], (string) $input['postcode']);
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
    }
}
