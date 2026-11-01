<?php

namespace App\Http\Requests;

use App\Community\CommunityAuthorizer;
use App\Models\Community;
use Illuminate\Foundation\Http\FormRequest;

class StorePostReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Community $community */
        $community = $this->route('community');

        return $this->user() !== null
            && app(CommunityAuthorizer::class)->canReply($this->user(), $community);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Write a reply before sending.',
        ];
    }
}
