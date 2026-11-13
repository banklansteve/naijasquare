<?php

namespace Tests\Feature;

use App\Community\SuggestNeedDetails;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeedDetailsSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_receives_template_suggestion_for_need_details(): void
    {
        [$community, $member] = $this->memberInCommunity([
            'city' => 'Manchester',
        ]);

        $this->actingAs($member)
            ->postJson(route('communities.posts.need-details-suggestion', $community), [
                'title' => 'Need a room near campus',
                'category_key' => 'housing',
                'location_label' => 'Fallowfield',
            ])
            ->assertOk()
            ->assertJsonPath('source', 'template')
            ->assertJsonPath('label', 'Suggested details')
            ->assertJsonFragment([
                'suggestion' => app(SuggestNeedDetails::class)->for(
                    'Need a room near campus',
                    'housing',
                    'Fallowfield',
                    $community,
                ),
            ]);
    }

    public function test_suggestion_includes_immigration_not_legal_advice_notice(): void
    {
        [$community, $member] = $this->memberInCommunity();

        $response = $this->actingAs($member)
            ->postJson(route('communities.posts.need-details-suggestion', $community), [
                'title' => 'Help with visa renewal paperwork',
                'category_key' => 'immigration',
            ])
            ->assertOk();

        $suggestion = (string) $response->json('suggestion');

        $this->assertStringContainsString('not legal advice', $suggestion);
        $this->assertStringContainsString('visa renewal paperwork', $suggestion);
    }

    public function test_guest_and_non_member_cannot_request_suggestion(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create();

        $this->postJson(route('communities.posts.need-details-suggestion', $community), [
            'title' => 'Need a room near campus',
            'category_key' => 'housing',
        ])->assertUnauthorized();

        $this->actingAs($outsider)
            ->postJson(route('communities.posts.need-details-suggestion', $community), [
                'title' => 'Need a room near campus',
                'category_key' => 'housing',
            ])
            ->assertForbidden();
    }

    public function test_suggestion_requires_title_and_category(): void
    {
        [$community, $member] = $this->memberInCommunity();

        $this->actingAs($member)
            ->postJson(route('communities.posts.need-details-suggestion', $community), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'category_key']);

        $this->actingAs($member)
            ->postJson(route('communities.posts.need-details-suggestion', $community), [
                'title' => 'Hi',
                'category_key' => 'not-a-real-category',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'category_key']);
    }

    /**
     * @param  array<string, mixed>  $communityAttributes
     * @return array{0: Community, 1: User}
     */
    private function memberInCommunity(array $communityAttributes = []): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create(array_merge([
            'who_can_post' => 'members',
        ], $communityAttributes));
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        return [$community, $member];
    }
}
