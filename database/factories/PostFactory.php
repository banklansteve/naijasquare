<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'user_id' => User::factory(),
            'type' => Post::TYPE_NEED,
            'title' => 'Looking for a room near the centre',
            'body' => 'Working full-time, quiet, and looking for something from next month.',
            'category_key' => 'housing',
            'location_label' => 'Sunderland',
            'urgency' => 'normal',
            'expires_at' => now()->addDays(14),
            'resolved_at' => null,
        ];
    }

    public function offer(): static
    {
        return $this->state(fn (): array => [
            'type' => Post::TYPE_OFFER,
            'title' => 'I can help with GP registration',
            'body' => 'Happy to walk someone through registering with a GP nearby.',
            'category_key' => 'healthcare',
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (): array => [
            'resolved_at' => now(),
        ]);
    }
}
