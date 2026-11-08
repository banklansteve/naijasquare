<?php

namespace Tests\Feature;

use App\Community\PostFormHints;
use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFormHintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_title_placeholder_uses_community_topic_and_place_for_a_need(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Dentists in Newcastle',
            'city' => 'Newcastle',
        ]);

        $hints = app(PostFormHints::class)->for($community, Post::TYPE_NEED);

        $this->assertSame('jobs', $hints['suggested_category_key']);
        $this->assertStringContainsString('dentist', strtolower($hints['title']));
        $this->assertStringContainsString('Newcastle', $hints['title']);
        $this->assertStringContainsString('Looking for', $hints['title']);
    }

    public function test_offer_placeholder_and_location_suggestions_include_profile_city(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Nigerians in Sunderland',
            'city' => 'Sunderland',
            'location_label' => 'Sunderland',
        ]);

        $hints = app(PostFormHints::class);
        $form = $hints->for($community, Post::TYPE_OFFER, 'everyday', 'Leeds');
        $locations = $hints->locationSuggestions($community, 'Leeds');

        $this->assertStringContainsString('Sunderland', $form['title']);
        $this->assertContains('Leeds', $locations);
        $this->assertContains('Sunderland', $locations);
        $this->assertContains('Newcastle', $locations);
    }
}
