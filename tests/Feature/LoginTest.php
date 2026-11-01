<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_sign_in_form(): void
    {
        $this->get('/sign-in')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')
            );
    }

    public function test_a_verified_person_can_sign_in_and_reach_home(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'x',
        ]);

        $this->post('/sign-in', [
            'email' => 'ada@example.com',
            'password' => 'x',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_unverified_person_is_sent_to_verify_email_after_sign_in(): void
    {
        User::factory()->unverified()->create([
            'email' => 'ada@example.com',
            'password' => 'x',
        ]);

        $this->post('/sign-in', [
            'email' => 'ada@example.com',
            'password' => 'x',
        ])->assertRedirect(route('signup.verify'));

        $this->assertAuthenticated();
    }

    public function test_wrong_details_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'secret',
        ]);

        $this->from('/sign-in')->post('/sign-in', [
            'email' => 'ada@example.com',
            'password' => 'wrong',
        ])->assertRedirect('/sign-in')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_forgot_password_sends_a_reset_link_without_revealing_whether_the_email_exists(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'ada@example.com',
        ]);

        $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'ada@example.com',
        ])->assertRedirect('/forgot-password')->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertRedirect('/forgot-password')->assertSessionHas('status');
    }

    public function test_a_person_can_reset_their_password_and_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'old',
        ]);

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'ada@example.com',
            'password' => 'new',
            'password_confirmation' => 'new',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->post('/sign-in', [
            'email' => 'ada@example.com',
            'password' => 'new',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
