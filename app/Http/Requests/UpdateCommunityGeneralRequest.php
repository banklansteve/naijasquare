<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityGeneralRequest extends FormRequest
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
        $tagKeys = array_keys(config('community.tags', []));

        return [
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'location_type' => ['required', Rule::in(array_keys(config('community.location_types', [])))],
            'location_label' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'tag_keys' => ['nullable', 'array', 'max:8'],
            'tag_keys.*' => [Rule::in($tagKeys)],
            'cover' => ['nullable', 'image', 'max:5120'],
            'admin_digest_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
