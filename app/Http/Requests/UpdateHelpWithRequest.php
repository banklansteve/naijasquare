<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHelpWithRequest extends FormRequest
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
            'help_category_keys' => ['required', 'array', 'min:1'],
            'help_category_keys.*' => ['string', Rule::in(array_keys(config('posts.categories', [])))],
            'help_note' => ['nullable', 'string', 'max:240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'help_category_keys.required' => 'Choose at least one area you can help with.',
            'help_category_keys.min' => 'Choose at least one area you can help with.',
        ];
    }
}
