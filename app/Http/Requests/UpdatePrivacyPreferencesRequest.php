<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrivacyPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_needs_replies' => $this->boolean('notify_needs_replies'),
            'notify_community' => $this->boolean('notify_community'),
            'notify_admin' => $this->boolean('notify_admin'),
            'prefer_product_emails' => $this->boolean('prefer_product_emails'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'profile_visibility' => ['required', 'string', Rule::in(['communities', 'private'])],
            'help_visibility' => ['required', 'string', Rule::in(['communities', 'private'])],
            'location_display' => ['required', 'string', Rule::in(['place', 'city', 'hidden'])],
            'notify_needs_replies' => ['required', 'boolean'],
            'notify_community' => ['required', 'boolean'],
            'notify_admin' => ['required', 'boolean'],
            'prefer_product_emails' => ['required', 'boolean'],
        ];
    }
}
