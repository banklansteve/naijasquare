<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'uid' => Str::upper(Str::random(8)),
            'name' => $first.' '.$last,
            'first_name' => $first,
            'last_name' => $last,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'house_number' => '12',
            'postcode' => 'SR5 3AA',
            'place_label' => 'Sunderland',
            'city' => 'Sunderland',
            'location_display' => 'place',
            'profile_visibility' => 'communities',
            'life_stage' => 'exploring',
            'interests' => ['community'],
            'help_category_keys' => null,
            'help_note' => null,
            'help_visibility' => 'communities',
            'bio' => null,
            'notify_needs_replies' => true,
            'notify_community' => true,
            'notify_admin' => true,
            'prefer_product_emails' => false,
        ];
    }

    /**
     * @param  list<string>  $categories
     */
    public function canHelpWith(array $categories = ['housing'], ?string $note = null): static
    {
        return $this->state(fn (): array => [
            'help_category_keys' => $categories,
            'help_note' => $note,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
