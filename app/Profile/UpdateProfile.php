<?php

namespace App\Profile;

use App\Models\User;
use App\Signup\PostcodeLookupFailed;
use App\Signup\PremiseDirectory;
use App\Signup\UkMobile;
use App\Signup\UnknownPostcode;
use App\Signup\UnknownPremise;
use Illuminate\Validation\ValidationException;

class UpdateProfile
{
    public function __construct(private PremiseDirectory $premises) {}

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     bio?: string|null,
     *     phone?: string|null,
     *     postcode?: string|null
     * }  $data
     */
    public function handle(User $user, array $data): User
    {
        $first = trim($data['first_name']);
        $last = trim($data['last_name']);
        $bio = isset($data['bio']) ? trim((string) $data['bio']) : null;
        $phone = $data['phone'] ?? null;

        $normalisedPhone = null;
        if (is_string($phone) && trim($phone) !== '') {
            $normalisedPhone = UkMobile::normalise(trim($phone));
        }

        $attributes = [
            'first_name' => $first,
            'last_name' => $last,
            'name' => trim($first.' '.$last),
            'bio' => $bio !== null && $bio !== '' ? $bio : null,
            'phone' => $normalisedPhone,
        ];

        $postcode = isset($data['postcode']) ? trim((string) $data['postcode']) : '';
        $currentPostcode = is_string($user->postcode) ? trim($user->postcode) : '';

        if ($postcode !== '' && strtoupper(str_replace(' ', '', $postcode)) !== strtoupper(str_replace(' ', '', $currentPostcode))) {
            $houseNumber = is_string($user->house_number) ? trim($user->house_number) : '';

            if ($houseNumber === '') {
                throw ValidationException::withMessages([
                    'postcode' => 'We need the house number from your account to update this postcode. Contact support if this looks wrong.',
                ]);
            }

            try {
                $confirmed = $this->premises->confirm($houseNumber, $postcode);
            } catch (UnknownPostcode) {
                throw ValidationException::withMessages([
                    'postcode' => 'We could not find that postcode. Check it and try again.',
                ]);
            } catch (UnknownPremise $exception) {
                throw ValidationException::withMessages([
                    'postcode' => $exception->getMessage(),
                ]);
            } catch (PostcodeLookupFailed) {
                throw ValidationException::withMessages([
                    'postcode' => 'We could not look that up just now. Try again in a moment.',
                ]);
            }

            $lookup = $confirmed->lookup();
            $attributes['postcode'] = $lookup['postcode'];
            $attributes['house_number'] = $lookup['house_number'];
            $attributes['address_line'] = $lookup['address'];
            $attributes['place_label'] = $lookup['place'];
            $attributes['city'] = $lookup['city'];
        }

        $user->forceFill($attributes)->save();

        return $user->refresh();
    }
}
