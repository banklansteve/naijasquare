<?php

namespace App\Http\Requests;

use App\Community\CommunityAuthorizer;
use App\Models\Community;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $categoryKeys = array_keys(config('posts.categories', []));
        $isOffer = $this->input('type') === Post::TYPE_OFFER;

        return [
            'type' => ['required', Rule::in([Post::TYPE_NEED, Post::TYPE_OFFER])],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'category_key' => [
                Rule::requiredIf(! $isOffer),
                'nullable',
                'string',
                Rule::in($categoryKeys),
            ],
            'category_keys' => [
                Rule::requiredIf($isOffer),
                'nullable',
                'array',
                'min:1',
                'max:3',
            ],
            'category_keys.*' => ['string', Rule::in($categoryKeys)],
            'location_label' => ['nullable', 'string', 'max:120'],
            'location_specific' => ['sometimes', 'boolean'],
            'location_labels' => [
                Rule::requiredIf($isOffer && $this->boolean('location_specific')),
                'nullable',
                'array',
                'max:3',
            ],
            'location_labels.*' => ['string', 'max:120'],
            'urgency' => [
                Rule::requiredIf(! $isOffer),
                'nullable',
                Rule::in(array_keys(config('posts.urgencies', []))),
            ],
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
            'category_keys.required' => 'Choose at least one category.',
            'category_keys.min' => 'Choose at least one category.',
            'category_keys.max' => 'Choose up to 3 categories.',
            'location_labels.required' => 'Add at least one area, or turn off location-specific.',
            'urgency.required' => 'Choose how soon you need this.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') !== Post::TYPE_OFFER || ! $this->boolean('location_specific')) {
                return;
            }

            $labels = collect($this->input('location_labels', []))
                ->filter(fn ($label): bool => is_string($label) && trim($label) !== '')
                ->values();

            if ($labels->isEmpty()) {
                $validator->errors()->add(
                    'location_labels',
                    'Add at least one area, or turn off location-specific.',
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('location_specific')) {
            $this->merge([
                'location_specific' => filter_var(
                    $this->input('location_specific'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE,
                ) ?? false,
            ]);
        }

        if (is_array($this->input('category_keys'))) {
            $this->merge([
                'category_keys' => array_values(array_unique(array_filter(
                    $this->input('category_keys'),
                    fn ($key): bool => is_string($key) && $key !== '',
                ))),
            ]);
        } elseif (
            $this->input('type') === Post::TYPE_OFFER
            && is_string($this->input('category_key'))
            && $this->input('category_key') !== ''
        ) {
            $this->merge([
                'category_keys' => [$this->input('category_key')],
            ]);
        }

        if (is_array($this->input('location_labels'))) {
            $this->merge([
                'location_labels' => array_values(array_unique(array_filter(
                    array_map(
                        fn ($label) => is_string($label) ? trim($label) : '',
                        $this->input('location_labels'),
                    ),
                    fn (string $label): bool => $label !== '',
                ))),
            ]);
        } elseif (
            $this->input('type') === Post::TYPE_OFFER
            && is_string($this->input('location_label'))
            && trim($this->input('location_label')) !== ''
        ) {
            $this->merge([
                'location_specific' => true,
                'location_labels' => [trim($this->input('location_label'))],
            ]);
        }
    }
}
