<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostReply>
 */
class PostReplyFactory extends Factory
{
    protected $model = PostReply::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'body' => 'I can help with this. Message me in the thread with a good time to talk.',
        ];
    }
}
