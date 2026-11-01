<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = 'Nigerians in '.fake()->unique()->city();

        $city = 'Sunderland';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => 'A place for people nearby to ask, offer, and meet.',
            'city' => $city,
            'interest_keys' => null,
            'listed' => true,
            'access_mode' => Community::ACCESS_REQUEST,
            'location_type' => 'city',
            'location_label' => $city,
            'tag_keys' => [],
            'require_guidelines_accept' => false,
            'who_can_post' => 'members',
            'member_list_visibility' => 'members',
            'admin_digest_enabled' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Community $community): void {
            if ($community->location_type === 'city' && filled($community->city)) {
                $community->location_label = $community->city;
            }
        });
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'access_mode' => Community::ACCESS_OPEN,
        ]);
    }

    public function inviteOnly(): static
    {
        return $this->state(fn (): array => [
            'access_mode' => Community::ACCESS_INVITE_ONLY,
        ]);
    }

    public function withOwner(?User $owner = null): static
    {
        return $this->afterCreating(function (Community $community) use ($owner): void {
            $user = $owner ?? User::factory()->create();
            $community->update(['created_by' => $user->id]);
            $community->members()->attach($user->id, [
                'role' => Community::ROLE_OWNER,
                'guidelines_accepted_at' => now(),
            ]);
        });
    }
}
