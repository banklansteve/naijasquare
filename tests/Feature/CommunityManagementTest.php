<?php

namespace Tests\Feature;

use App\Mail\CommunityAdminInvite;
use App\Models\Community;
use App\Models\CommunityAdminInvitation;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommunityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_create_a_community_as_owner_with_request_mode_default(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $longDescription = trim(str_repeat('A calm place to ask for help and meet people nearby. ', 20));

        $this->actingAs($user)
            ->post('/communities', [
                'name' => 'Nigerians in Sunderland',
                'description' => $longDescription,
                'location_type' => 'city',
                'location_label' => 'Sunderland',
                'city' => 'Sunderland',
                'tag_keys' => ['housing'],
                'access_mode' => 'request',
                'who_can_post' => 'members',
                'guidelines' => 'Be respectful.',
                'cover' => UploadedFile::fake()->image('cover.jpg', 800, 400),
            ])
            ->assertRedirect();

        $community = Community::query()->where('name', 'Nigerians in Sunderland')->first();

        $this->assertNotNull($community);
        $this->assertSame('request', $community->access_mode);
        $this->assertSame('members', $community->who_can_post);
        $this->assertSame($user->id, $community->created_by);
        $this->assertSame($longDescription, $community->description);
        $this->assertNotNull($community->cover_path);
        Storage::disk('local')->assertExists($community->cover_path);
        $this->assertDatabaseHas('community_user', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_guest_and_unverified_users_cannot_create_communities(): void
    {
        $this->post('/communities', [
            'name' => 'Blocked Community',
            'description' => 'Should not be created at all.',
            'location_type' => 'city',
            'location_label' => 'Sunderland',
            'access_mode' => 'open',
        ])->assertRedirect(route('login'));

        $unverified = User::factory()->unverified()->create();

        $this->actingAs($unverified)
            ->post('/communities', [
                'name' => 'Blocked Community',
                'description' => 'Should not be created at all.',
                'location_type' => 'city',
                'location_label' => 'Sunderland',
                'access_mode' => 'open',
            ])
            ->assertRedirect(route('signup.verify'));

        $this->assertDatabaseMissing('communities', ['name' => 'Blocked Community']);
    }

    public function test_join_open_request_and_invite_only_modes(): void
    {
        $owner = User::factory()->create();
        $joiner = User::factory()->create();

        $open = Community::factory()->open()->withOwner($owner)->create(['name' => 'Open Group']);
        $request = Community::factory()->withOwner($owner)->create(['name' => 'Request Group']);
        $inviteOnly = Community::factory()->inviteOnly()->withOwner($owner)->create(['name' => 'Invite Group']);

        $this->actingAs($joiner)
            ->post(route('communities.join', $open))
            ->assertRedirect();

        $this->assertDatabaseHas('community_user', [
            'community_id' => $open->id,
            'user_id' => $joiner->id,
            'role' => 'member',
        ]);

        $this->actingAs($joiner)
            ->post(route('communities.join', $request), ['note' => 'I live nearby'])
            ->assertRedirect();

        $this->assertDatabaseHas('community_join_requests', [
            'community_id' => $request->id,
            'user_id' => $joiner->id,
            'status' => 'pending',
            'note' => 'I live nearby',
        ]);
        $this->assertDatabaseMissing('community_user', [
            'community_id' => $request->id,
            'user_id' => $joiner->id,
        ]);

        $this->actingAs($joiner)
            ->from(route('communities.show', $inviteOnly))
            ->post(route('communities.join', $inviteOnly))
            ->assertSessionHasErrors('community');
    }

    public function test_admin_can_approve_and_decline_join_requests_but_member_cannot(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $applicant = User::factory()->create();
        $other = User::factory()->create();

        $community = Community::factory()->withOwner($owner)->create();
        $community->members()->attach($member->id, ['role' => 'member']);

        $approveRequest = CommunityJoinRequest::query()->create([
            'community_id' => $community->id,
            'user_id' => $applicant->id,
            'note' => 'Please',
            'status' => 'pending',
        ]);

        $declineRequest = CommunityJoinRequest::query()->create([
            'community_id' => $community->id,
            'user_id' => $other->id,
            'status' => 'pending',
        ]);

        $this->actingAs($member)
            ->post(route('communities.settings.join-requests.approve', [$community, $approveRequest]))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('communities.settings.join-requests.approve', [$community, $approveRequest]))
            ->assertRedirect();

        $this->assertDatabaseHas('community_user', [
            'community_id' => $community->id,
            'user_id' => $applicant->id,
            'role' => 'member',
        ]);
        $this->assertSame('approved', $approveRequest->fresh()->status);

        $this->actingAs($owner)
            ->post(route('communities.settings.join-requests.decline', [$community, $declineRequest]))
            ->assertRedirect();

        $this->assertSame('declined', $declineRequest->fresh()->status);
        $this->assertDatabaseMissing('community_user', [
            'community_id' => $community->id,
            'user_id' => $other->id,
        ]);
    }

    public function test_admin_invite_sends_email_and_accept_grants_moderator(): void
    {
        Mail::fake();

        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'admin.invitee@example.com']);
        $community = Community::factory()->withOwner($owner)->create(['name' => 'Invite Admins Group']);

        $this->actingAs($owner)
            ->post(route('communities.settings.admins.invite', $community), [
                'email' => $invitee->email,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Admin invitation sent.');

        Mail::assertSent(CommunityAdminInvite::class);

        $invitation = CommunityAdminInvitation::query()->where('email', $invitee->email)->first();
        $this->assertNotNull($invitation);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $invitee->id,
            'type' => 'community_admin_invite',
        ]);

        $this->actingAs($invitee)
            ->post(route('invitations.admin.accept', $invitation->token))
            ->assertRedirect(route('communities.show', $community));

        $this->assertDatabaseHas('community_user', [
            'community_id' => $community->id,
            'user_id' => $invitee->id,
            'role' => 'moderator',
        ]);
        $this->assertSame('accepted', $invitation->fresh()->status);
    }

    public function test_member_cannot_open_danger_zone_or_delete(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->withOwner($owner)->create();
        $community->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->get(route('communities.settings', [$community, 'danger']))
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('communities.destroy', $community), [
                'confirm_name' => $community->name,
            ])
            ->assertForbidden();
    }

    public function test_community_show_page_renders_for_verified_member(): void
    {
        $owner = User::factory()->create();
        $community = Community::factory()->withOwner($owner)->create([
            'name' => 'Show Page Group',
            'guidelines' => 'Be kind.',
        ]);

        $this->actingAs($owner)
            ->get(route('communities.show', $community))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Show')
                ->where('community.name', 'Show Page Group')
                ->where('membership.is_member', true)
                ->where('membership.role', 'owner')
                ->where('membership.can_post', true)
                ->has('posts', 0)
            );
    }

    public function test_discover_suggests_local_and_uk_wide_communities_matching_interests(): void
    {
        $host = User::factory()->create();

        $localMatch = Community::factory()->open()->withOwner($host)->create([
            'name' => 'Cardiff Housing Circle',
            'city' => 'Cardiff',
            'interest_keys' => ['housing'],
        ]);
        $localWrongInterest = Community::factory()->withOwner($host)->create([
            'name' => 'Cardiff Networking',
            'city' => 'Cardiff',
            'interest_keys' => ['networking'],
        ]);
        $otherCity = Community::factory()->withOwner($host)->create([
            'name' => 'Manchester Newcomers',
            'city' => 'Manchester',
            'interest_keys' => ['housing'],
        ]);
        $ukWide = Community::factory()->withOwner($host)->create([
            'name' => 'UK Housing Tips',
            'city' => 'UK',
            'location_type' => 'uk_wide',
            'location_label' => 'UK-wide',
            'interest_keys' => ['housing'],
        ]);

        $user = User::factory()->create([
            'city' => 'Cardiff',
            'interests' => ['housing'],
        ]);

        $this->actingAs($user)
            ->get(route('communities.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Index')
                ->where('discoverHint', 'Suggested for Cardiff and your interests')
                ->has('discover', 2)
                ->where('discover.0.name', fn (string $name): bool => in_array($name, [
                    $localMatch->name,
                    $ukWide->name,
                ], true))
                ->where('discover', fn ($discover): bool => collect($discover)->pluck('name')->contains($localMatch->name)
                    && collect($discover)->pluck('name')->contains($ukWide->name)
                    && ! collect($discover)->pluck('name')->contains($localWrongInterest->name)
                    && ! collect($discover)->pluck('name')->contains($otherCity->name))
            );
    }
}
