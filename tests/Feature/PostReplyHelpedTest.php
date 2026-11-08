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

class PostReplyHelpedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_need_author_cannot_reply_and_safety_flag_still_payload_for_others(): void
    {
        [$owner, $seeker, $helper, $community] = $this->setupCommunity();

        $need = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $seeker->id,
            'type' => Post::TYPE_NEED,
            'category_key' => 'housing',
            'category_keys' => ['housing'],
        ]);

        $this->actingAs($seeker)
            ->get(route('communities.posts.show', [$community, $need]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Show')
                ->where('permissions.can_reply', false)
                ->where('permissions.is_author', true)
                ->where('permissions.can_mark_helped', true)
                ->where('post.requires_safety_warning', true)
                ->has('post.created_at_human')
            );

        $this->actingAs($seeker)
            ->from(route('communities.posts.show', [$community, $need]))
            ->post(route('communities.posts.replies.store', [$community, $need]), [
                'body' => 'Replying to my own Need should fail.',
            ])
            ->assertSessionHasErrors('body');

        $this->actingAs($helper)
            ->get(route('communities.posts.show', [$community, $need]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('permissions.can_reply', true)
                ->where('permissions.is_author', false)
                ->where('permissions.can_mark_helped', false)
            );
    }

    public function test_mark_helped_flow_and_sort_order(): void
    {
        [$owner, $seeker, $helper, $community] = $this->setupCommunity();
        $other = User::factory()->create();
        $community->members()->attach($other->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $need = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $seeker->id,
            'type' => Post::TYPE_NEED,
        ]);

        $earlier = PostReply::factory()->create([
            'post_id' => $need->id,
            'user_id' => $helper->id,
            'body' => 'Earlier reply',
            'created_at' => now()->subHour(),
        ]);
        $later = PostReply::factory()->create([
            'post_id' => $need->id,
            'user_id' => $other->id,
            'body' => 'Later reply that helped',
            'created_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($helper)
            ->from(route('communities.posts.show', [$community, $need]))
            ->post(route('communities.posts.replies.helped', [$community, $need, $later]))
            ->assertSessionHasErrors('reply');

        $this->actingAs($seeker)
            ->post(route('communities.posts.replies.helped', [$community, $need, $later]))
            ->assertRedirect();

        $this->assertNotNull($later->fresh()->helped_at);
        $this->assertSame($seeker->id, $later->fresh()->helped_by_user_id);

        $this->actingAs($seeker)
            ->get(route('communities.posts.show', [$community, $need]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('replies.0.id', $later->id)
                ->where('replies.0.is_helped', true)
                ->where('replies.1.id', $earlier->id)
                ->where('replies.1.is_helped', false)
            );

        $this->actingAs($seeker)
            ->delete(route('communities.posts.replies.helped.destroy', [$community, $need, $later]))
            ->assertRedirect();

        $this->assertNull($later->fresh()->helped_at);
    }

    public function test_offer_replies_cannot_be_marked_helped(): void
    {
        [$owner, $seeker, $helper, $community] = $this->setupCommunity();

        $offer = Post::factory()->offer()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
        ]);
        $reply = PostReply::factory()->create([
            'post_id' => $offer->id,
            'user_id' => $seeker->id,
            'body' => 'I need this help please.',
        ]);

        $this->actingAs($helper)
            ->from(route('communities.posts.show', [$community, $offer]))
            ->post(route('communities.posts.replies.helped', [$community, $offer, $reply]))
            ->assertSessionHasErrors('reply');
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: Community}
     */
    private function setupCommunity(): array
    {
        $owner = User::factory()->create();
        $seeker = User::factory()->create();
        $helper = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'who_can_post' => 'members',
        ]);

        foreach ([$seeker, $helper] as $member) {
            $community->members()->attach($member->id, [
                'role' => Community::ROLE_MEMBER,
                'guidelines_accepted_at' => now(),
            ]);
        }

        return [$owner, $seeker, $helper, $community];
    }
}
