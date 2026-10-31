<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinCommunitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->email_verified_at !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'communities' => ['required', 'array', 'min:1'],
            'communities.*' => ['integer', 'distinct'],
        ];
    }
}
