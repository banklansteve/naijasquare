<?php

namespace App\Http\Requests;

use App\Rules\UkMobileNumber;
use App\Signup\UkMobile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        if (is_string($phone)) {
            $phone = trim($phone);
            $phone = $phone === '' ? null : (UkMobile::normalise($phone) ?? $phone);
        }

        $this->merge([
            'phone' => $phone,
            'postcode' => is_string($this->input('postcode'))
                ? strtoupper(trim($this->input('postcode')))
                : $this->input('postcode'),
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
            'bio' => ['nullable', 'string', 'max:280'],
            'phone' => ['nullable', 'string', 'max:30', new UkMobileNumber],
            'postcode' => ['nullable', 'string', 'max:12'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Add your first name.',
            'last_name.required' => 'Add your last name.',
            'bio.max' => 'Keep your bio to 280 characters.',
        ];
    }
}
