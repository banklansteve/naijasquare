<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RelatedOffersFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_need_create_and_show_surface_related_offers(): void
    {
        [$owner, $helper, $seeker, $community] = $this->communityTrio();

        $offer = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
            'type' => Post::TYPE_OFFER,
            'title' => 'I can help with dentist CV reviews',
            'category_key' => 'jobs',
            'location_label' => 'Newcastle',
        ]);

        $this->actingAs($seeker)
            ->get(route('communities.posts.create', ['community' => $community, 'type' => 'need']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Create')
                ->has('relatedOffers')
            );

        $this->actingAs($seeker)
            ->getJson(route('communities.related-offers', [
                'community' => $community,
                'category_key' => 'jobs',
                'location_label' => 'Newcastle',
            ]))
            ->assertOk()
            ->assertJsonPath('offers.0.id', $offer->id)
            ->assertJsonPath('offers.0.title', 'I can help with dentist CV reviews');

        $need = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $seeker->id,
            'type' => Post::TYPE_NEED,
            'title' => 'Looking for a dentist job around Newcastle',
            'category_key' => 'jobs',
            'location_label' => 'Newcastle',
        ]);

        $this->actingAs($seeker)
            ->get(route('communities.posts.show', [$community, $need]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Show')
                ->has('relatedOffers', 1)
                ->where('relatedOffers.0.id', $offer->id)
            );
    }

    public function test_posting_a_need_notifies_matching_offer_authors(): void
    {
        [$owner, $helper, $seeker, $community] = $this->communityTrio();

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
            'type' => Post::TYPE_OFFER,
            'title' => 'I can mentor new dentists',
            'category_key' => 'jobs',
            'location_label' => 'Newcastle',
        ]);

        $this->actingAs($seeker)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Looking for a dentist job around Newcastle',
                'body' => 'I am newly qualified and need advice on NHS applications in Newcastle.',
                'category_key' => 'jobs',
                'location_label' => 'Newcastle',
                'urgency' => 'normal',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $helper->id,
            'type' => 'related_need',
        ]);

        $notification = AppNotification::query()->where('user_id', $helper->id)->first();
        $this->assertNotNull($notification);
        $this->assertSame('Looking for a dentist job around Newcastle', $notification->data['need_title'] ?? null);

        $this->actingAs($helper)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Notifications/Index')
                ->has('notifications', 1)
                ->where('notifications.0.type', 'related_need')
            );

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_offer_author_is_not_notified_about_unrelated_categories(): void
    {
        [$owner, $helper, $seeker, $community] = $this->communityTrio();

        Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
            'type' => Post::TYPE_OFFER,
            'category_key' => 'housing',
            'title' => 'I can help find flats',
        ]);

        $this->actingAs($seeker)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Looking for a dentist job',
                'body' => 'Need career advice for dentistry roles.',
                'category_key' => 'jobs',
                'urgency' => 'normal',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $helper->id,
            'type' => 'related_need',
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: Community}
     */
    private function communityTrio(): array
    {
        $owner = User::factory()->create();
        $helper = User::factory()->create();
        $seeker = User::factory()->create(['city' => 'Newcastle']);
        $community = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Dentists in Newcastle',
            'city' => 'Newcastle',
            'who_can_post' => 'members',
        ]);

        foreach ([$helper, $seeker] as $member) {
            $community->members()->attach($member->id, [
                'role' => Community::ROLE_MEMBER,
                'guidelines_accepted_at' => now(),
            ]);
        }

        return [$owner, $helper, $seeker, $community];
    }
}
