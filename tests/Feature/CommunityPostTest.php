<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommunityPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_member_can_create_need_and_offer_when_who_can_post_is_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['city' => 'Leeds']);
        $community = Community::factory()->open()->withOwner($owner)->create([
            'city' => 'Leeds',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Need a room near campus',
                'body' => 'Looking for a quiet room from next month, working full-time.',
                'category_key' => 'housing',
                'location_label' => 'Leeds',
                'urgency' => 'normal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'type' => 'need',
            'category_key' => 'housing',
            'title' => 'Need a room near campus',
        ]);

        $this->actingAs($member)
            ->post(route('communities.posts.store', $community), [
                'type' => 'offer',
                'title' => 'I can help with GP registration',
                'body' => 'Happy to walk someone through registering with a local GP.',
                'category_key' => 'healthcare',
                'location_label' => 'Leeds',
                'urgency' => 'low',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'type' => 'offer',
        ]);
    }

    public function test_member_cannot_create_post_when_who_can_post_is_admins(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'who_can_post' => 'admins',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Should not post',
                'body' => 'This should be blocked for ordinary members.',
                'category_key' => 'everyday',
                'urgency' => 'normal',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Admin can post this need',
                'body' => 'Owners may post when posting is limited to admins.',
                'category_key' => 'everyday',
                'urgency' => 'normal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'title' => 'Admin can post this need',
        ]);
    }

    public function test_guidelines_must_be_accepted_before_posting(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'require_guidelines_accept' => true,
            'guidelines' => 'Be kind.',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => null,
        ]);

        $this->actingAs($member)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Blocked until guidelines',
                'body' => 'Should fail until guidelines are accepted.',
                'category_key' => 'everyday',
                'urgency' => 'normal',
            ])
            ->assertForbidden();
    }

    public function test_member_can_reply_and_author_can_resolve(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'who_can_post' => 'admins',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'type' => Post::TYPE_NEED,
        ]);

        $this->actingAs($member)
            ->post(route('communities.posts.replies.store', [$community, $post]), [
                'body' => 'I can help with this next week.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('post_replies', [
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'I can help with this next week.',
        ]);

        $this->actingAs($owner)
            ->post(route('communities.posts.resolve', [$community, $post]))
            ->assertRedirect();

        $this->assertNotNull($post->fresh()->resolved_at);
    }

    public function test_member_can_report_a_post_once(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create();
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'category_key' => 'housing',
        ]);

        $this->actingAs($member)
            ->from(route('communities.posts.show', [$community, $post]))
            ->post(route('reports.store'), [
                'reportable_type' => 'post',
                'reportable_id' => $post->id,
                'reason_key' => 'scam',
                'note' => 'Asked for a deposit up front.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $member->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason_key' => 'scam',
            'status' => Report::STATUS_PENDING,
        ]);

        $this->actingAs($member)
            ->from(route('communities.posts.show', [$community, $post]))
            ->post(route('reports.store'), [
                'reportable_type' => 'post',
                'reportable_id' => $post->id,
                'reason_key' => 'scam',
            ])
            ->assertSessionHasErrors('report');
    }

    public function test_community_show_lists_posts_and_create_form_shows_safety_categories(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'who_can_post' => 'members',
        ]);

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'title' => 'Need a flatshare',
            'category_key' => 'housing',
        ]);

        $this->actingAs($owner)
            ->get(route('communities.show', $community))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Show')
                ->where('membership.can_post', true)
                ->has('posts', 1)
                ->where('posts.0.title', 'Need a flatshare')
            );

        $this->actingAs($owner)
            ->get(route('communities.posts.create', ['community' => $community, 'type' => 'need']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Create')
                ->where('type', 'need')
                ->has('categories')
                ->where('categories.0.key', 'housing')
                ->where('categories.0.safety_warning', true)
                ->where('safetyNotice', config('posts.safety_notice'))
            );
    }

    public function test_guest_and_non_member_cannot_create_posts(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create();

        $this->post(route('communities.posts.store', $community), [
            'type' => 'need',
            'title' => 'Guest post',
            'body' => 'Should not be allowed at all here.',
            'category_key' => 'everyday',
            'urgency' => 'normal',
        ])->assertRedirect(route('login'));

        $this->actingAs($outsider)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Outsider post',
                'body' => 'Should not be allowed without membership.',
                'category_key' => 'everyday',
                'urgency' => 'normal',
            ])
            ->assertForbidden();
    }
}
