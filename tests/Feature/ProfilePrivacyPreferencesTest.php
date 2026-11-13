<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePrivacyPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_privacy_and_notification_preferences(): void
    {
        $user = User::factory()->create([
            'location_display' => 'place',
            'profile_visibility' => 'communities',
            'help_visibility' => 'communities',
            'notify_needs_replies' => true,
            'notify_community' => true,
            'notify_admin' => true,
            'prefer_product_emails' => false,
        ]);

        $this->actingAs($user)
            ->put(route('profile.privacy'), [
                'profile_visibility' => 'private',
                'help_visibility' => 'private',
                'location_display' => 'hidden',
                'notify_needs_replies' => false,
                'notify_community' => true,
                'notify_admin' => false,
                'prefer_product_emails' => true,
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('private', $user->profile_visibility);
        $this->assertSame('private', $user->help_visibility);
        $this->assertSame('hidden', $user->location_display);
        $this->assertNull($user->displayLocation());
        $this->assertFalse($user->notify_needs_replies);
        $this->assertTrue($user->notify_community);
        $this->assertFalse($user->notify_admin);
        $this->assertTrue($user->prefer_product_emails);
        $this->assertFalse($user->wantsNotification('related_need'));
        $this->assertFalse($user->wantsNotification('community_admin_invite'));
    }
}
