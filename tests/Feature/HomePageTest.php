<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_home_shows_greeting_invite_first_week_and_ask_offer(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ada',
            'city' => 'Sunderland',
            'uid' => 'ADA00001',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('firstName', 'Ada')
                ->where('city', 'Sunderland')
                ->where('askHref', '/communities')
                ->where('offerHref', '/communities')
                ->has('invite.url')
                ->where('invite.url', fn (string $url): bool => str_contains($url, 'invited_by=ADA00001'))
                ->has('invite.whatsapp_url')
                ->has('firstWeek.steps', 3)
                ->where('firstWeek.steps.0.key', 'join')
                ->where('firstWeek.steps.0.done', false)
                ->where('firstWeek.active_key', 'join')
                ->has('myPosts', 0)
                ->has('helpableNeeds', 0)
                ->has('yourCommunities', 0)
                ->has('administeredCommunities', 0)
                ->where('nearCommunitiesEmpty', true)
                ->where('categoryFilter', null)
            );
    }

    public function test_home_splits_member_and_admin_communities_and_limits_your_list(): void
    {
        $owner = User::factory()->create(['city' => 'Leeds']);
        $member = User::factory()->create(['city' => 'Leeds']);

        Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Leeds Owners Circle',
            'city' => 'Leeds',
        ]);
        $joined = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Leeds Members Circle',
            'city' => 'Leeds',
        ]);
        $joined->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        Post::factory()->create([
            'community_id' => $joined->id,
            'user_id' => $owner->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
        ]);

        $this->actingAs($owner)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('administeredCommunities', 2)
                ->has('yourCommunities', 0)
                ->where('administeredCommunities.0.role_label', fn (string $label) => in_array($label, ['Owner', 'Moderator'], true))
                ->has('administeredCommunities.0.monogram.initials')
            );

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('administeredCommunities', 0)
                ->has('yourCommunities', 1)
                ->where('yourCommunities.0.name', 'Leeds Members Circle')
                ->where('yourCommunities.0.role_label', 'Member')
                ->where('yourCommunities.0.open_needs_count', 1)
                ->where('yourCommunities.0.pinned', false)
                ->where('askHref', '/communities/'.$joined->slug.'/posts/create?type=need')
                ->where('offerHref', '/communities/'.$joined->slug.'/posts/create?type=offer')
            );
    }

    public function test_home_lists_own_open_posts(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create(['city' => 'Leeds']);
        $community = Community::factory()->open()->withOwner($owner)->create([
            'city' => 'Leeds',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($user->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $openNeed = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => Post::TYPE_NEED,
            'title' => 'Need a quiet room',
            'category_key' => 'housing',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => Post::TYPE_OFFER,
            'title' => 'I can review CVs',
            'category_key' => 'jobs',
        ]);

        Post::factory()->resolved()->create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => Post::TYPE_NEED,
            'title' => 'Already resolved',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('myPosts', 2)
                ->where('myPosts', fn ($posts): bool => collect($posts)->contains(
                    fn (array $post): bool => $post['id'] === $openNeed->id && $post['status'] === 'open',
                ))
            );
    }

    public function test_first_week_hides_when_complete_or_dismissed(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->canHelpWith(['healthcare'], 'Nursing')->create([
            'city' => 'Sunderland',
        ]);

        $first = Community::factory()->open()->withOwner($owner)->create(['city' => 'Sunderland']);
        $second = Community::factory()->open()->withOwner($owner)->create(['city' => 'Sunderland']);

        $first->members()->attach($user->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);
        $second->members()->attach($user->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        Post::factory()->create([
            'community_id' => $first->id,
            'user_id' => $user->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'everyday',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('firstWeek', null)
            );

        $newcomer = User::factory()->create(['city' => 'Sunderland']);

        $this->actingAs($newcomer)
            ->post(route('home.checklist.dismiss'))
            ->assertRedirect();

        $this->assertNotNull($newcomer->fresh()->home_checklist_dismissed_at);

        $this->actingAs($newcomer)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('firstWeek', null)
            );
    }
}
