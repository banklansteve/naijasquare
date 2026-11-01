<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_community_teaser_but_not_invite_only_or_unlisted(): void
    {
        $host = User::factory()->create();

        $public = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Professionals in Leeds',
            'city' => 'Leeds',
            'description' => 'A network for professionals settling in Leeds.',
            'interest_keys' => ['professionals'],
            'tag_keys' => ['professionals'],
        ]);

        $inviteOnly = Community::factory()->inviteOnly()->withOwner($host)->create([
            'name' => 'Private Leeds Circle',
            'city' => 'Leeds',
            'listed' => true,
        ]);

        $unlisted = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Hidden Leeds Group',
            'city' => 'Leeds',
            'listed' => false,
        ]);

        $this->get(route('communities.show', $public))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/PublicShow')
                ->where('community.name', 'Professionals in Leeds')
                ->where('community.location_label', 'Leeds')
                ->missing('membership')
            );

        $this->get(route('communities.show', $inviteOnly))->assertNotFound();
        $this->get(route('communities.show', $unlisted))->assertNotFound();
    }

    public function test_authenticated_member_still_sees_full_community_show(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->withOwner($owner)->create([
            'name' => 'Show Page Group',
        ]);

        $this->actingAs($owner)
            ->get(route('communities.show', $community))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Show')
                ->where('community.name', 'Show Page Group')
                ->where('membership.is_member', true)
            );
    }

    public function test_explore_place_lists_only_matching_public_communities(): void
    {
        $host = User::factory()->create();

        $leeds = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Professionals in Leeds',
            'city' => 'Leeds',
            'interest_keys' => ['professionals'],
            'tag_keys' => ['professionals'],
        ]);

        Community::factory()->withOwner($host)->create([
            'name' => 'Manchester Newcomers',
            'city' => 'Manchester',
            'interest_keys' => ['new_arrivals'],
            'tag_keys' => ['new_arrivals'],
        ]);

        Community::factory()->inviteOnly()->withOwner($host)->create([
            'name' => 'Leeds Invite Only',
            'city' => 'Leeds',
        ]);

        $this->get(route('explore.place', 'leeds'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Explore/Place')
                ->where('place.label', 'Leeds')
                ->has('communities', 1)
                ->where('communities.0.name', $leeds->name)
            );

        $this->get(route('explore.tag', 'professionals'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Explore/Tag')
                ->where('tag.key', 'professionals')
                ->has('communities', 1)
                ->where('communities.0.name', $leeds->name)
            );

        $this->get(route('explore.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Explore/Index')
                ->has('places')
                ->has('tags')
            );
    }

    public function test_sitemap_includes_public_urls_and_omits_invite_only(): void
    {
        $host = User::factory()->create();

        $public = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Professionals in Leeds',
            'city' => 'Leeds',
            'tag_keys' => ['professionals'],
            'interest_keys' => ['professionals'],
        ]);

        $inviteOnly = Community::factory()->inviteOnly()->withOwner($host)->create([
            'name' => 'Secret Leeds',
            'city' => 'Leeds',
        ]);

        $response = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $response->assertSee(route('explore.index'), false);
        $response->assertSee(route('explore.place', 'leeds'), false);
        $response->assertSee(route('explore.tag', 'professionals'), false);
        $response->assertSee(route('communities.show', $public), false);
        $response->assertDontSee(route('communities.show', $inviteOnly), false);
    }

    public function test_guest_can_fetch_cover_for_public_community_only(): void
    {
        Storage::fake('local');

        $host = User::factory()->create();
        $file = UploadedFile::fake()->image('cover.jpg', 800, 400);

        $public = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Cover Public',
            'city' => 'Leeds',
            'cover_path' => $file->store('community-covers', 'local'),
        ]);

        $inviteOnly = Community::factory()->inviteOnly()->withOwner($host)->create([
            'name' => 'Cover Private',
            'city' => 'Leeds',
            'cover_path' => $file->store('community-covers', 'local'),
        ]);

        $this->get(route('communities.cover', $public))->assertOk();
        $this->get(route('communities.cover', $inviteOnly))->assertNotFound();
    }
}
