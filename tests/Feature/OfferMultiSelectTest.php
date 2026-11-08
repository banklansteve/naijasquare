<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferMultiSelectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_offer_can_have_multiple_categories_without_location_or_urgency(): void
    {
        [$owner, $helper, $community] = $this->memberSetup();

        $this->actingAs($helper)
            ->post(route('communities.posts.store', $community), [
                'type' => 'offer',
                'title' => 'I can help with visas and paperwork',
                'body' => 'Happy to answer immigration and legal paperwork questions remotely.',
                'category_keys' => ['immigration', 'legal'],
                'location_specific' => false,
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $offer = Post::query()->where('type', Post::TYPE_OFFER)->first();
        $this->assertNotNull($offer);
        $this->assertSame('immigration', $offer->category_key);
        $this->assertSame(['immigration', 'legal'], $offer->category_keys);
        $this->assertNull($offer->location_label);
        $this->assertSame([], $offer->location_labels);
        $this->assertSame('normal', $offer->urgency);

        $this->actingAs($helper)
            ->get(route('communities.posts.show', [$community, $offer]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Show')
                ->where('post.category_keys', ['immigration', 'legal'])
                ->where('post.location_specific', false)
                ->where('post.urgency_label', null)
            );
    }

    public function test_location_specific_offer_requires_areas_and_accepts_up_to_three(): void
    {
        [$owner, $helper, $community] = $this->memberSetup();

        $this->actingAs($helper)
            ->post(route('communities.posts.store', $community), [
                'type' => 'offer',
                'title' => 'I can help find flats in the North East',
                'body' => 'I know agents and viewing tips around Newcastle and Sunderland.',
                'category_keys' => ['housing'],
                'location_specific' => true,
                'location_labels' => [],
            ])
            ->assertSessionHasErrors('location_labels');

        $this->actingAs($helper)
            ->post(route('communities.posts.store', $community), [
                'type' => 'offer',
                'title' => 'I can help find flats in the North East',
                'body' => 'I know agents and viewing tips around Newcastle and Sunderland.',
                'category_keys' => ['housing'],
                'location_specific' => true,
                'location_labels' => ['Newcastle', 'Sunderland'],
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $offer = Post::query()->where('type', Post::TYPE_OFFER)->first();
        $this->assertNotNull($offer);
        $this->assertSame(['Newcastle', 'Sunderland'], $offer->location_labels);
        $this->assertSame('Newcastle', $offer->location_label);
    }

    public function test_need_still_uses_single_category_and_urgency(): void
    {
        [$owner, $seeker, $community] = $this->memberSetup();

        $this->actingAs($seeker)
            ->post(route('communities.posts.store', $community), [
                'type' => 'need',
                'title' => 'Need a dentist job around Newcastle',
                'body' => 'Looking for NHS dentist roles and interview tips in Newcastle.',
                'category_key' => 'jobs',
                'location_label' => 'Newcastle',
                'urgency' => 'urgent',
            ])
            ->assertRedirect();

        $need = Post::query()->where('type', Post::TYPE_NEED)->first();
        $this->assertNotNull($need);
        $this->assertSame('jobs', $need->category_key);
        $this->assertSame(['jobs'], $need->category_keys);
        $this->assertSame(['Newcastle'], $need->location_labels);
        $this->assertSame('urgent', $need->urgency);
    }

    public function test_related_offers_match_any_offer_category_including_anywhere_offers(): void
    {
        [$owner, $helper, $seeker, $community] = $this->communityTrio();

        Post::factory()->offer()->categories(['immigration', 'legal'])->anywhere()->create([
            'community_id' => $community->id,
            'user_id' => $helper->id,
            'title' => 'Visa and paperwork help',
        ]);

        $this->actingAs($seeker)
            ->getJson(route('communities.related-offers', [
                'community' => $community,
                'category_key' => 'legal',
                'location_label' => 'Leeds',
            ]))
            ->assertOk()
            ->assertJsonPath('offers.0.title', 'Visa and paperwork help');
    }

    /**
     * @return array{0: User, 1: User, 2: Community}
     */
    private function memberSetup(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['city' => 'Newcastle']);
        $community = Community::factory()->open()->withOwner($owner)->create([
            'city' => 'Newcastle',
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        return [$owner, $member, $community];
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: Community}
     */
    private function communityTrio(): array
    {
        $owner = User::factory()->create();
        $helper = User::factory()->create();
        $seeker = User::factory()->create(['city' => 'Leeds']);
        $community = Community::factory()->open()->withOwner($owner)->create([
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
