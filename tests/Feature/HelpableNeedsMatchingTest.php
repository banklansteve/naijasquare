<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HelpableNeedsMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_helpable_needs_rank_category_match_and_unanswered_first(): void
    {
        $owner = User::factory()->create();
        $helper = User::factory()->canHelpWith(['housing'])->create([
            'city' => 'Sunderland',
        ]);
        $author = User::factory()->create(['city' => 'Sunderland']);

        $community = Community::factory()->open()->withOwner($owner)->create([
            'city' => 'Sunderland',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($helper->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);
        $community->members()->attach($author->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $matchedOpen = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
            'title' => 'Need a room near the station',
            'location_label' => 'Sunderland',
            'created_at' => now()->subHour(),
        ]);

        $otherCategory = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'jobs',
            'title' => 'Need CV feedback',
            'location_label' => 'Sunderland',
            'created_at' => now(),
        ]);

        $matchedAnswered = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
            'title' => 'Already has a reply',
            'location_label' => 'Sunderland',
            'created_at' => now()->subMinutes(30),
        ]);
        PostReply::query()->create([
            'post_id' => $matchedAnswered->id,
            'user_id' => $owner->id,
            'body' => 'I can help with viewing times this week.',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
            'title' => 'Own need should be hidden',
        ]);

        $this->actingAs($helper)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('helpableNeeds', 3)
                ->where('helpableNeeds.0.id', $matchedOpen->id)
                ->where('helpableNeeds.0.match_reason', 'Matches what you can help with')
                ->where('helpableNeeds.1.id', $matchedAnswered->id)
                ->where('helpableNeeds.2.id', $otherCategory->id)
            );
    }

    public function test_category_filter_limits_helpable_needs(): void
    {
        $owner = User::factory()->create();
        $helper = User::factory()->canHelpWith(['housing', 'jobs'])->create([
            'city' => 'Leeds',
        ]);
        $author = User::factory()->create();

        $community = Community::factory()->open()->withOwner($owner)->create([
            'city' => 'Leeds',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach([$helper->id, $author->id], [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
            'title' => 'Housing need',
        ]);
        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'jobs',
            'title' => 'Jobs need',
        ]);

        $this->actingAs($helper)
            ->get(route('home', ['category' => 'jobs']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categoryFilter', 'jobs')
                ->has('helpableNeeds', 1)
                ->where('helpableNeeds.0.category_key', 'jobs')
            );
    }
}
