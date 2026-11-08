<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateHelpWithTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_save_help_with_categories_and_note(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.help-with'), [
                'help_category_keys' => ['housing', 'jobs'],
                'help_note' => 'CV reviews and room hunting in Leeds',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame(['housing', 'jobs'], $user->help_category_keys);
        $this->assertSame('CV reviews and room hunting in Leeds', $user->help_note);
    }

    public function test_help_with_requires_at_least_one_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('home'))
            ->put(route('profile.help-with'), [
                'help_category_keys' => [],
                'help_note' => 'Anything',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('help_category_keys');
    }

    public function test_help_with_rejects_unknown_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.help-with'), [
                'help_category_keys' => ['not-a-real-category'],
            ])
            ->assertSessionHasErrors('help_category_keys.0');
    }
}
