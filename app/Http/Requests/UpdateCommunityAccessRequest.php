<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityAccessRequest extends FormRequest
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
            'access_mode' => ['required', Rule::in(array_keys(config('community.access_modes', [])))],
            'who_can_post' => ['required', Rule::in(array_keys(config('community.who_can_post', [])))],
            'member_list_visibility' => ['required', Rule::in(array_keys(config('community.member_list_visibility', [])))],
        ];
    }
}
