<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeInviteAndPinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_personal_invite_link_attributes_new_signups(): void
    {
        $this->fakePostcode();

        $inviter = User::factory()->create([
            'uid' => 'HOST1234',
            'first_name' => 'Chioma',
        ]);

        $this->get(route('register', ['invited_by' => 'HOST1234']))
            ->assertOk();

        $this->post('/join', $this->account())
            ->assertRedirect(route('signup.verify'));

        $invitee = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($invitee);
        $this->assertSame($inviter->id, $invitee->invited_by_user_id);
    }

    public function test_invite_share_endpoint_returns_whatsapp_payload(): void
    {
        $user = User::factory()->create([
            'uid' => 'SHARE001',
            'first_name' => 'Ada',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('invite.share'))
            ->assertOk()
            ->assertJsonStructure(['url', 'message', 'whatsapp_url']);

        $this->assertStringContainsString('invited_by=SHARE001', $response->json('url'));
        $this->assertStringContainsString('Ada invited you', $response->json('message'));
    }

    public function test_member_can_pin_and_unpin_a_community_for_home(): void
    {
        $owner = User::factory()->create(['city' => 'Leeds']);
        $member = User::factory()->create(['city' => 'Leeds']);

        $pinned = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Pinned Leeds',
            'city' => 'Leeds',
        ]);
        $other = Community::factory()->open()->withOwner($owner)->create([
            'name' => 'Other Leeds',
            'city' => 'Leeds',
        ]);

        $pinned->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);
        $other->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('communities.pin', $pinned))
            ->assertRedirect();

        $this->assertNotNull(
            $member->communities()->where('communities.id', $pinned->id)->first()?->pivot->pinned_at,
        );

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('yourCommunities', 2)
                ->where('yourCommunities.0.name', 'Pinned Leeds')
                ->where('yourCommunities.0.pinned', true)
            );

        $this->actingAs($member)
            ->post(route('communities.pin', $pinned))
            ->assertRedirect();

        $this->assertNull(
            $member->communities()->where('communities.id', $pinned->id)->first()?->pivot->pinned_at,
        );
    }

    public function test_non_members_cannot_pin_communities(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create();

        $this->actingAs($stranger)
            ->from(route('home'))
            ->post(route('communities.pin', $community))
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('community');
    }

    /**
     * @return array<string, mixed>
     */
    private function account(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ada',
            'last_name' => 'Okeke',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '',
            'house_number' => '12',
            'postcode' => 'SR4 0JL',
            'life_stage' => 'exploring',
            'interests' => ['housing', 'community'],
            'terms' => '1',
        ], $overrides);
    }

    private function fakePostcode(string $buildingNumber = '12'): void
    {
        Http::fake([
            'api.postcodes.io/*' => Http::response([
                'status' => 200,
                'result' => [
                    'postcode' => 'SR4 0JL',
                    'admin_district' => 'Sunderland',
                    'admin_ward' => 'Pennywell & South Hylton',
                    'parish' => 'Sunderland, unparished area',
                ],
            ]),
            'api.ideal-postcodes.co.uk/*' => Http::response([
                'code' => 2000,
                'total' => 1,
                'result' => [
                    [
                        'postcode' => 'SR4 0JL',
                        'post_town' => 'Sunderland',
                        'building_number' => $buildingNumber,
                        'building_name' => '',
                        'sub_building_name' => '',
                        'premise' => $buildingNumber,
                        'line_1' => $buildingNumber.' Hylton Bank',
                        'line_2' => '',
                        'line_3' => '',
                    ],
                ],
            ]),
        ]);
    }
}
