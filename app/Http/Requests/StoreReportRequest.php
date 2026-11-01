<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->email_verified_at !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reportable_type' => ['required', Rule::in(['post', 'reply'])],
            'reportable_id' => ['required', 'integer', 'min:1'],
            'reason_key' => ['required', Rule::in(array_keys(config('posts.report_reasons', [])))],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason_key.required' => 'Choose a reason for the report.',
        ];
    }
}
