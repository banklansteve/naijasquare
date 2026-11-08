<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommunityReportModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_owner_sees_pending_reports_in_moderation_settings(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();

        Report::factory()->forPost($post, $member)->create([
            'reason_key' => 'scam',
            'note' => 'Asking for a deposit',
        ]);

        $this->actingAs($owner)
            ->get(route('communities.settings', [$community, 'moderation']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Settings')
                ->where('permissions.can_moderate', true)
                ->has('reports', 1)
                ->where('reports.0.reason_key', 'scam')
                ->where('reports.0.title', $post->title)
                ->where('reports.0.note', 'Asking for a deposit')
            );
    }

    public function test_member_cannot_open_settings_or_act_on_reports(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();
        $report = Report::factory()->forPost($post, $member)->create();

        $this->actingAs($member)
            ->get(route('communities.settings', [$community, 'moderation']))
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('communities.settings.reports.dismiss', [$community, $report]))
            ->assertForbidden();

        $this->assertSame(Report::STATUS_PENDING, $report->fresh()->status);
    }

    public function test_moderator_can_dismiss_a_report_without_hiding_content(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();
        $moderator = User::factory()->create();
        $community->members()->attach($moderator->id, [
            'role' => Community::ROLE_MODERATOR,
            'guidelines_accepted_at' => now(),
        ]);

        $report = Report::factory()->forPost($post, $member)->create();

        $this->actingAs($moderator)
            ->from(route('communities.settings', [$community, 'moderation']))
            ->post(route('communities.settings.reports.dismiss', [$community, $report]))
            ->assertRedirect(route('communities.settings', [$community, 'moderation']));

        $report->refresh();
        $this->assertSame(Report::STATUS_DISMISSED, $report->status);
        $this->assertSame(Report::RESOLUTION_DISMISS, $report->resolution_key);
        $this->assertSame($moderator->id, $report->reviewed_by_user_id);
        $this->assertNull($post->fresh()->hidden_at);
    }

    public function test_owner_can_remove_reported_content_and_hide_it_from_members(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();
        $secondReporter = User::factory()->create();
        $community->members()->attach($secondReporter->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $first = Report::factory()->forPost($post, $member)->create(['reason_key' => 'spam']);
        $second = Report::factory()->forPost($post, $secondReporter)->create(['reason_key' => 'scam']);

        $this->actingAs($owner)
            ->post(route('communities.settings.reports.remove', [$community, $first]))
            ->assertRedirect();

        $this->assertNotNull($post->fresh()->hidden_at);
        $this->assertSame(Report::STATUS_REMOVED, $first->fresh()->status);
        $this->assertSame(Report::STATUS_REMOVED, $second->fresh()->status);

        $this->actingAs($member)
            ->get(route('communities.posts.show', [$community, $post]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Removed')
            );

        $this->actingAs($owner)
            ->get(route('communities.posts.show', [$community, $post]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Show')
                ->where('isHidden', true)
            );

        $this->actingAs($member)
            ->get(route('communities.show', $community))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('posts', 0)
            );
    }

    public function test_removing_a_reported_reply_hides_only_that_reply(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();
        $reply = PostReply::factory()->create([
            'post_id' => $post->id,
            'user_id' => $member->id,
            'body' => 'Send money to this account',
        ]);

        $report = Report::query()->create([
            'community_id' => $community->id,
            'reporter_id' => $owner->id,
            'reportable_type' => PostReply::class,
            'reportable_id' => $reply->id,
            'reason_key' => 'scam',
            'status' => Report::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->post(route('communities.settings.reports.remove', [$community, $report]))
            ->assertRedirect();

        $this->assertNotNull($reply->fresh()->hidden_at);
        $this->assertNull($post->fresh()->hidden_at);

        $this->actingAs($member)
            ->get(route('communities.posts.show', [$community, $post]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Communities/Posts/Show')
                ->has('replies', 0)
            );
    }

    public function test_report_from_another_community_cannot_be_actioned_here(): void
    {
        [$owner, $member, $community, $post] = $this->communityWithPost();
        $otherOwner = User::factory()->create();
        $other = Community::factory()->open()->withOwner($otherOwner)->create();
        $otherPost = Post::factory()->create([
            'community_id' => $other->id,
            'user_id' => $otherOwner->id,
        ]);
        $report = Report::factory()->forPost($otherPost, $otherOwner)->create();

        $this->actingAs($owner)
            ->post(route('communities.settings.reports.remove', [$community, $report]))
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: User, 2: Community, 3: Post}
     */
    private function communityWithPost(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = Community::factory()->open()->withOwner($owner)->create([
            'who_can_post' => 'members',
        ]);
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $post = Post::factory()->create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'title' => 'Need a deposit for a flat',
            'category_key' => 'housing',
        ]);

        return [$owner, $member, $community, $post];
    }
}
