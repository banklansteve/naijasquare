<?php

namespace App\Http\Requests;

use App\Rules\UkMobileNumber;
use App\Signup\UkMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        if (is_string($phone)) {
            $phone = trim($phone);
            $phone = $phone === '' ? null : (UkMobile::normalise($phone) ?? $phone);
        }

        $this->merge([
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'phone' => $phone,
            'interests' => array_values(array_unique((array) $this->input('interests', []))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30', new UkMobileNumber],
            'house_number' => ['required', 'string', 'max:20'],
            'postcode' => ['required', 'string', 'max:12'],
            'life_stage' => ['required', 'string', Rule::in(array_keys(config('signup.life_stages')))],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['string', Rule::in(array_keys(config('signup.interests')))],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => 'Confirm you are 18 or over and agree to the guidelines and privacy notice.',
            'interests.min' => 'Choose at least one interest.',
        ];
    }
}
