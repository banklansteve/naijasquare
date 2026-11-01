<?php

namespace App\Http\Requests;

use App\Community\CommunityAuthorizer;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Community $community */
        $community = $this->route('community');

        return $this->user() !== null
            && app(CommunityAuthorizer::class)->canCreatePost($this->user(), $community);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([Post::TYPE_NEED, Post::TYPE_OFFER])],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'category_key' => ['required', Rule::in(array_keys(config('posts.categories', [])))],
            'location_label' => ['nullable', 'string', 'max:120'],
            'urgency' => ['required', Rule::in(array_keys(config('posts.urgencies', [])))],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Add a short title.',
            'body.required' => 'Say a bit more so people know how to help.',
            'category_key.required' => 'Choose a category.',
            'urgency.required' => 'Choose how soon you need this.',
        ];
    }
}
