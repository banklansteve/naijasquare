<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Nigerians in '.fake()->city(),
            'description' => 'A place for people nearby to ask, offer, and meet.',
            'city' => 'Sunderland',
            'interest_keys' => null,
            'listed' => true,
        ];
    }
}
