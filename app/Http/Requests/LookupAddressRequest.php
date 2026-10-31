<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'house_number' => ['required', 'string', 'max:20'],
            'postcode' => ['required', 'string', 'max:12'],
        ];
    }
}
