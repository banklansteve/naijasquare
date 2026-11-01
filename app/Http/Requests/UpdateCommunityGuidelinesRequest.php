<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityGuidelinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guidelines' => ['nullable', 'string', 'max:5000'],
            'require_guidelines_accept' => ['required', 'boolean'],
        ];
    }
}
