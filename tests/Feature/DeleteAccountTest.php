<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use App\Profile\DeleteAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_soft_delete_account_and_posts_show_former_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create([
            'name' => 'Ada Okafor',
            'email' => 'ada@example.com',
            'password' => Hash::make('password'),
            'phone' => '+447700900123',
            'bio' => 'Hello',
            'place_label' => 'Hylton Castle, Sunderland',
            'city' => 'Sunderland',
        ]);

        $community = Community::factory()->open()->withOwner($owner)->create();
        $community->members()->attach($member->id, [
            'role' => Community::ROLE_MEMBER,
            'guidelines_accepted_at' => now(),
        ]);

        $post = Post::factory()->for($community)->for($member, 'author')->create([
            'type' => Post::TYPE_NEED,
            'title' => 'Need a room',
        ]);

        PostReply::factory()->for($post)->for($member, 'author')->create([
            'body' => 'I can help with this.',
        ]);

        $this->actingAs($member)
            ->delete(route('profile.destroy'), [
                'current_password' => 'password',
                'confirmation' => 'DELETE',
            ])
            ->assertRedirect(route('landing'));

        $this->assertGuest();

        $member->refresh();
        $this->assertSoftDeleted($member);
        $this->assertSame(DeleteAccount::ANONYMOUS_NAME, $member->name);
        $this->assertNull($member->phone);
        $this->assertNull($member->bio);
        $this->assertStringStartsWith('deleted+', $member->email);

        $this->assertSame(
            DeleteAccount::ANONYMOUS_NAME,
            $post->fresh()->author->name,
        );
        $this->assertSame(
            DeleteAccount::ANONYMOUS_NAME,
            $post->fresh()->replies()->first()->author->name,
        );
    }

    public function test_delete_requires_password_and_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->from(route('profile'))
            ->delete(route('profile.destroy'), [
                'current_password' => 'wrong',
                'confirmation' => 'DELETE',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->from(route('profile'))
            ->delete(route('profile.destroy'), [
                'current_password' => 'password',
                'confirmation' => 'delete',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors('confirmation');

        $this->assertNull($user->fresh()->deleted_at);
    }
}
