<?php

namespace App\Http\Requests;

use App\Community\CommunityAuthorizer;
use App\Models\Community;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuggestNeedDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Community $community */
        $community = $this->route('community');

        return $this->user() !== null
            && app(CommunityAuthorizer::class)->isMember($this->user(), $community);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'category_key' => ['required', 'string', Rule::in(array_keys(config('posts.categories', [])))],
            'location_label' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Add a title first so we can suggest details.',
            'title.min' => 'Add a slightly clearer title first.',
            'category_key.required' => 'Choose a category first.',
        ];
    }
}
