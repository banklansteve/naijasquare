<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'reporter_id' => User::factory(),
            'reportable_type' => Post::class,
            'reportable_id' => Post::factory(),
            'reason_key' => 'spam',
            'note' => null,
            'status' => Report::STATUS_PENDING,
        ];
    }

    public function forPost(Post $post, ?User $reporter = null): static
    {
        return $this->state(fn (): array => [
            'community_id' => $post->community_id,
            'reporter_id' => $reporter?->id ?? User::factory(),
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
        ]);
    }
}
