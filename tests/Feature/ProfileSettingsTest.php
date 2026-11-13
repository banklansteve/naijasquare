<?php

namespace Tests\Feature;

use App\Models\User;
use App\Signup\ConfirmedAddress;
use App\Signup\Place;
use App\Signup\PremiseDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_for_verified_member(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ada',
            'last_name' => 'Okafor',
            'name' => 'Ada Okafor',
            'place_label' => 'Hylton Castle, Sunderland',
            'city' => 'Sunderland',
            'postcode' => 'SR5 3AA',
            'location_display' => 'place',
            'bio' => 'Happy to help with GP registration.',
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Show')
                ->where('profile.first_name', 'Ada')
                ->where('profile.display_location', 'Hylton Castle, Sunderland')
                ->where('profile.postcode', 'SR5 3AA')
                ->where('profile.bio', 'Happy to help with GP registration.')
                ->has('helpCategories'));
    }

    public function test_member_can_update_profile_details_without_changing_postcode(): void
    {
        $user = User::factory()->create([
            'house_number' => '12',
            'postcode' => 'SR5 3AA',
            'place_label' => 'Hylton Castle, Sunderland',
            'city' => 'Sunderland',
            'phone' => null,
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'first_name' => 'Chioma',
                'last_name' => 'Bello',
                'bio' => 'New to the North East.',
                'phone' => '07700900123',
                'postcode' => 'SR5 3AA',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('Chioma Bello', $user->name);
        $this->assertSame('New to the North East.', $user->bio);
        $this->assertSame('+447700900123', $user->phone);
        $this->assertSame('SR5 3AA', $user->postcode);
        $this->assertSame('Hylton Castle, Sunderland', $user->place_label);
    }

    public function test_member_can_update_postcode_and_refresh_area(): void
    {
        $user = User::factory()->create([
            'house_number' => '12',
            'postcode' => 'SR5 3AA',
            'place_label' => 'Hylton Castle, Sunderland',
            'city' => 'Sunderland',
        ]);

        $this->mock(PremiseDirectory::class, function ($mock): void {
            $mock->shouldReceive('confirm')
                ->once()
                ->with('12', 'NE1 4ST')
                ->andReturn(new ConfirmedAddress(
                    addressLine: '12 Grey Street, Newcastle upon Tyne, NE1 4ST',
                    place: new Place(
                        postcode: 'NE1 4ST',
                        city: 'Newcastle upon Tyne',
                        area: 'City Centre',
                        latitude: 54.97,
                        longitude: -1.61,
                    ),
                    houseNumber: '12',
                    street: 'Grey Street',
                ));
        });

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'bio' => null,
                'phone' => null,
                'postcode' => 'NE1 4ST',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('NE1 4ST', $user->postcode);
        $this->assertSame('Newcastle upon Tyne', $user->city);
        $this->assertSame('City Centre, Newcastle upon Tyne', $user->place_label);
    }

    public function test_member_can_update_location_display(): void
    {
        $user = User::factory()->create([
            'city' => 'Sunderland',
            'place_label' => 'Hylton Castle, Sunderland',
            'location_display' => 'place',
        ]);

        $this->actingAs($user)
            ->put(route('profile.location-display'), [
                'location_display' => 'hidden',
            ])
            ->assertRedirect();

        $this->assertSame('hidden', $user->fresh()->location_display);
        $this->assertNull($user->fresh()->displayLocation());
    }

    public function test_member_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-secret-99', $user->fresh()->password));
    }

    public function test_member_can_confirm_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->postJson(route('profile.password.confirm'), [
                'current_password' => 'password',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($user)
            ->postJson(route('profile.password.confirm'), [
                'current_password' => 'wrong-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_email_change_requires_reverification(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->put(route('profile.email'), [
                'email' => 'ada.new@example.com',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('signup.verify'));

        $user->refresh();

        $this->assertSame('ada.new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_member_can_upload_profile_photo(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['photo_path' => null]);
        $file = UploadedFile::fake()->image('portrait.jpg', 400, 400);

        $this->actingAs($user)
            ->post(route('profile.photo'), [
                'photo' => $file,
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->photo_path);
        Storage::disk('local')->assertExists($user->photo_path);
    }

    public function test_guest_cannot_open_profile(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
