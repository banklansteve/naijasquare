<?php

namespace App\Http\Requests;

use App\Rules\UkMobileNumber;
use App\Signup\UkMobile;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneRequest extends FormRequest
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
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30', new UkMobileNumber],
        ];
    }
}
