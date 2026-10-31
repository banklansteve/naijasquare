<?php

namespace Tests\Feature;

use App\Mail\VerifyEmailCode;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SignupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Http::preventStrayRequests();
    }

    public function test_a_person_can_join_with_a_short_password_and_confirm_by_code(): void
    {
        $this->fakePostcode();

        $this->post('/join', $this->account([
            'password' => 'x',
            'password_confirmation' => 'x',
        ]))->assertRedirect(route('signup.verify'));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Ada Okeke', $user->name);
        $this->assertSame('12', $user->house_number);
        $this->assertSame('SR4 0JL', $user->postcode);
        $this->assertSame('12 Hylton Bank, Sunderland, SR4 0JL', $user->address_line);
        $this->assertSame('Pennywell & South Hylton, Sunderland', $user->place_label);
        $this->assertSame('Sunderland', $user->city);
        $this->assertSame(['housing', 'community'], $user->interests);

        $code = $this->sentCode();

        $this->post('/join/verify', ['code' => $code])
            ->assertRedirect(route('signup.communities'));

        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->get(route('signup.communities'))->assertRedirect(route('home'));

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('firstName', 'Ada')
                ->where('city', 'Sunderland')
                ->has('communities', 0)
                ->missing('place')
                ->missing('address_line')
                ->missing('house_number')
                ->missing('postcode')
            );
    }

    public function test_address_lookup_returns_the_area_and_city_only_as_the_public_place(): void
    {
        $this->fakePostcode();

        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'sr40jl',
        ])->assertOk()->assertExactJson([
            'address' => '12 Hylton Bank, Sunderland, SR4 0JL',
            'place' => 'Pennywell & South Hylton, Sunderland',
            'house_number' => '12',
            'street' => 'Hylton Bank',
            'postcode' => 'SR4 0JL',
            'city' => 'Sunderland',
        ]);
    }

    public function test_address_lookup_uses_the_open_map_when_the_premises_service_is_unavailable(): void
    {
        Http::fake([
            'api.postcodes.io/*' => Http::response([
                'status' => 200,
                'result' => [
                    'postcode' => 'SW1A 2AA',
                    'admin_district' => 'Westminster',
                    'admin_ward' => "St James's",
                    'parish' => '',
                ],
            ]),
            'api.ideal-postcodes.co.uk/*' => Http::response([
                'code' => 4021,
                'message' => 'Lookup Limit Reached',
            ], 402),
            'photon.komoot.io/*' => Http::response([
                'features' => [
                    [
                        'properties' => [
                            'housenumber' => '10',
                            'street' => 'Downing Street',
                            'postcode' => 'SW1A 2AA',
                            'city' => 'London',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->postJson('/join/address', [
            'house_number' => '10',
            'postcode' => 'SW1A 2AA',
        ])->assertOk()->assertExactJson([
            'address' => '10 Downing Street, London, SW1A 2AA',
            'place' => "St James's, Westminster",
            'house_number' => '10',
            'street' => 'Downing Street',
            'postcode' => 'SW1A 2AA',
            'city' => 'Westminster',
        ]);

        Http::fake([
            'api.postcodes.io/*' => Http::response([
                'status' => 200,
                'result' => [
                    'postcode' => 'SW1A 2AA',
                    'admin_district' => 'Westminster',
                    'admin_ward' => "St James's",
                    'parish' => '',
                ],
            ]),
            'api.ideal-postcodes.co.uk/*' => Http::response([
                'code' => 4021,
                'message' => 'Lookup Limit Reached',
            ], 402),
            'photon.komoot.io/*' => Http::response([
                'features' => [
                    [
                        'properties' => [
                            'name' => 'SW1A 2AA',
                            'postcode' => 'SW1A 2AA',
                            'city' => 'London',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->postJson('/join/address', [
            'house_number' => '999',
            'postcode' => 'SW1A 2AA',
        ])->assertOk()->assertExactJson([
            'address' => "999, St James's, Westminster, SW1A 2AA",
            'place' => "St James's, Westminster",
            'house_number' => '999',
            'street' => null,
            'postcode' => 'SW1A 2AA',
            'city' => 'Westminster',
        ]);
    }

    public function test_address_lookup_shows_the_street_at_the_postcode(): void
    {
        Http::fake([
            'api.postcodes.io/*' => Http::response([
                'status' => 200,
                'result' => [
                    'postcode' => 'SR4 0NP',
                    'admin_district' => 'Sunderland',
                    'admin_ward' => 'Pennywell & South Hylton',
                    'parish' => 'Sunderland, unparished area',
                    'latitude' => 54.902143,
                    'longitude' => -1.452558,
                ],
            ]),
            'api.ideal-postcodes.co.uk/*' => Http::response([
                'code' => 4021,
                'message' => 'Lookup Limit Reached',
            ], 402),
            'photon.komoot.io/*' => Http::response([
                'features' => [],
            ]),
            'nominatim.openstreetmap.org/*' => Http::sequence()
                ->push([
                    'lat' => '54.9019693',
                    'lon' => '-1.4524559',
                    'address' => [
                        'road' => 'Southside Gardens',
                        'postcode' => 'SR4 0NP',
                        'city' => 'Sunderland',
                    ],
                ])
                ->push([
                    'lat' => '51.5074',
                    'lon' => '-0.1278',
                    'address' => [
                        'road' => 'Baker Street',
                        'postcode' => 'W1U 6AA',
                    ],
                ]),
        ]);

        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'SR4 0NP',
        ])->assertOk()->assertExactJson([
            'address' => '12 Southside Gardens, Sunderland, SR4 0NP',
            'place' => 'Pennywell & South Hylton, Sunderland',
            'house_number' => '12',
            'street' => 'Southside Gardens',
            'postcode' => 'SR4 0NP',
            'city' => 'Sunderland',
        ]);

        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'SR4 0NP',
        ])->assertOk()->assertExactJson([
            'address' => '12, Pennywell & South Hylton, Sunderland, SR4 0NP',
            'place' => 'Pennywell & South Hylton, Sunderland',
            'house_number' => '12',
            'street' => null,
            'postcode' => 'SR4 0NP',
            'city' => 'Sunderland',
        ]);
    }

    public function test_a_house_number_that_is_not_at_the_postcode_is_rejected(): void
    {
        $this->fakePostcode(buildingNumber: '4');

        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'SR4 0JL',
        ])->assertStatus(422)->assertJsonValidationErrors('house_number');
    }

    public function test_a_shared_house_number_asks_for_the_flat(): void
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
                'total' => 2,
                'result' => [
                    $this->premise('12', 'Flat 1'),
                    $this->premise('12', 'Flat 2'),
                ],
            ]),
        ]);

        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'SR4 0JL',
        ])->assertStatus(422)->assertJsonValidationErrors('house_number');

        $this->postJson('/join/address', [
            'house_number' => 'Flat 1',
            'postcode' => 'SR4 0JL',
        ])->assertOk()->assertJsonPath('address', 'Flat 1 12 Hylton Bank, Sunderland, SR4 0JL');
    }

    public function test_an_optional_phone_must_be_a_uk_mobile_when_it_is_given(): void
    {
        $this->from('/join')->post('/join', $this->account([
            'phone' => '020 7946 0958',
        ]))->assertRedirect('/join')->assertSessionHasErrors('phone');

        $this->assertGuest();

        $this->fakePostcode();

        $this->post('/join', $this->account([
            'phone' => '07123 456789',
        ]))->assertRedirect(route('signup.verify'));

        $this->assertSame('+447123456789', User::query()->where('email', 'ada@example.com')->value('phone'));
    }

    public function test_an_unknown_postcode_is_rejected_without_calling_the_lookup(): void
    {
        $this->postJson('/join/address', [
            'house_number' => '12',
            'postcode' => 'not a postcode',
        ])->assertStatus(422)->assertJsonValidationErrors('postcode');
    }

    public function test_password_mismatch_and_duplicate_email_are_rejected(): void
    {
        User::factory()->create(['email' => 'ada@example.com']);

        $this->from('/join')->post('/join', $this->account([
            'password' => 'x',
            'password_confirmation' => 'y',
        ]))->assertRedirect('/join')->assertSessionHasErrors('password');

        $this->assertGuest();

        $this->from('/join')->post('/join', $this->account())
            ->assertRedirect('/join')
            ->assertSessionHasErrors('email');
    }

    public function test_a_wrong_code_expires_after_five_tries_and_a_new_code_replaces_it(): void
    {
        $this->fakePostcode();
        $this->post('/join', $this->account());
        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();
        $original = $this->sentCode();

        foreach (range(1, 4) as $attempt) {
            $this->from(route('signup.verify'))
                ->post('/join/verify', ['code' => '000000'])
                ->assertRedirect(route('signup.verify'))
                ->assertSessionHasErrors('code');
        }

        $this->from(route('signup.verify'))
            ->post('/join/verify', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->fresh()->email_verified_at);
        $this->post('/join/verify', ['code' => $original])->assertSessionHasErrors('code');

        RateLimiter::clear('email-code:'.$user->id);

        $this->post(route('signup.verify.resend'))->assertRedirect();

        $fresh = null;
        Mail::assertSent(VerifyEmailCode::class, function (VerifyEmailCode $mail) use (&$fresh) {
            $fresh = $mail->code;

            return true;
        });

        $this->post('/join/verify', ['code' => $fresh])->assertRedirect(route('signup.communities'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_an_expired_code_is_rejected(): void
    {
        $this->fakePostcode();
        $this->post('/join', $this->account());
        $code = $this->sentCode();

        $this->travel(11)->minutes();

        $this->from(route('signup.verify'))
            ->post('/join/verify', ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertNull(User::query()->where('email', 'ada@example.com')->firstOrFail()->email_verified_at);
    }

    public function test_the_verification_email_is_branded_and_contains_the_code(): void
    {
        $this->fakePostcode();
        $this->post('/join', $this->account());

        Mail::assertSent(VerifyEmailCode::class, function (VerifyEmailCode $mail) {
            $html = preg_replace('/\s+/', '', $mail->render()) ?? '';

            $digits = collect(str_split($mail->code))->every(
                fn (string $digit): bool => str_contains($html, '>'.$digit.'<'),
            );

            return $digits
                && str_contains($html, (string) config('app.name'))
                && str_contains($html, 'Checkyouremail');
        });
    }

    public function test_a_profile_photo_is_stored_without_the_original_file(): void
    {
        Storage::fake('local');
        $this->fakePostcode();

        $this->post('/join', $this->account([
            'photo' => UploadedFile::fake()->image('me.jpg'),
        ]))->assertRedirect(route('signup.verify'));

        $path = User::query()->where('email', 'ada@example.com')->firstOrFail()->photo_path;

        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_needs_and_resources_are_quiet_pages_for_verified_people(): void
    {
        $this->get('/needs')->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)->get('/needs')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guide')
                ->where('title', 'Ask for help')
            );

        $this->actingAs($user)->get('/resources')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guide')
                ->where('title', 'Explore local resources')
            );
    }

    public function test_unverified_people_cannot_open_home_or_join_a_community(): void
    {
        $user = User::factory()->unverified()->create();
        $community = Community::factory()->create();
        $community->members()->attach(User::factory()->create());

        $this->actingAs($user)->get('/home')->assertRedirect(route('signup.verify'));
        $this->actingAs($user)->get('/join')->assertRedirect(route('signup.verify'));
        $this->actingAs($user)->post('/join/communities', [
            'communities' => [$community->id],
        ])->assertRedirect(route('signup.verify'));

        $this->assertFalse($user->fresh()->communities->contains($community));
    }

    public function test_only_live_matching_communities_are_offered_and_joining_is_optional(): void
    {
        $host = User::factory()->create();
        $match = Community::factory()->create([
            'name' => 'Nigerians in Sunderland',
            'description' => 'Practical help around the city.',
            'city' => 'Sunderland',
            'interest_keys' => ['housing'],
        ]);
        $match->members()->attach($host);

        $otherInterest = Community::factory()->create([
            'name' => 'Healthcare workers in Sunderland',
            'city' => 'Sunderland',
            'interest_keys' => ['networking'],
        ]);
        $otherInterest->members()->attach($host);

        $empty = Community::factory()->create([
            'name' => 'New arrivals in Sunderland',
            'city' => 'Sunderland',
            'interest_keys' => ['housing'],
        ]);

        $leeds = Community::factory()->create([
            'name' => 'Nigerians in Leeds',
            'city' => 'Leeds',
        ]);
        $leeds->members()->attach($host);

        $member = User::factory()->create([
            'city' => 'Sunderland',
            'interests' => ['housing', 'community'],
        ]);

        $this->actingAs($member)->get('/home')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('city', 'Sunderland')
                ->has('communities', 1)
                ->where('communities.0.name', 'Nigerians in Sunderland')
                ->where('communities.0.description', 'Practical help around the city.')
                ->where('communities.0.members', 1)
                ->missing('address_line')
            );

        $this->actingAs($member)->get('/join/communities')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Communities')
                ->has('communities', 1)
                ->where('communities.0.name', 'Nigerians in Sunderland')
                ->where('communities.0.members', 1)
                ->where('communities.0.description', 'Practical help around the city.')
            );

        $this->actingAs($member)->post('/join/communities', [
            'communities' => [$leeds->id],
        ])->assertSessionHasErrors('communities');

        $this->assertFalse($member->fresh()->communities->contains($match));
        $this->assertFalse($member->fresh()->communities->contains($empty));

        $this->actingAs($member)->post('/join/communities', [
            'communities' => [$match->id],
        ])->assertRedirect(route('home'));

        $this->assertTrue($member->fresh()->communities->contains($match));
        $this->assertSame(2, $match->fresh()->members()->count());

        $this->actingAs($member)->get('/home')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('communities', 0)
            );
    }

    public function test_verification_email_is_marked_up_with_one_box_per_digit(): void
    {
        $html = (new VerifyEmailCode('483920'))->render();

        $this->assertStringContainsString('Check your email', $html);
        $this->assertStringContainsString("We've sent a verification code to your email.", $html);

        foreach (str_split('483920') as $digit) {
            $this->assertStringContainsString('>'.$digit.'<', preg_replace('/\s+/', '', $html) ?? $html);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function account(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ada',
            'last_name' => 'Okeke',
            'email' => 'ada@example.com',
            'password' => 'x',
            'password_confirmation' => 'x',
            'phone' => '',
            'house_number' => '12',
            'postcode' => 'SR4 0JL',
            'life_stage' => 'new_arrival',
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
                    $this->premise($buildingNumber),
                ],
            ]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function premise(string $buildingNumber, string $subBuilding = ''): array
    {
        return [
            'postcode' => 'SR4 0JL',
            'post_town' => 'Sunderland',
            'building_number' => $buildingNumber,
            'building_name' => '',
            'sub_building_name' => $subBuilding,
            'premise' => trim($subBuilding.' '.$buildingNumber),
            'line_1' => trim($subBuilding.' '.$buildingNumber.' Hylton Bank'),
            'line_2' => '',
            'line_3' => '',
        ];
    }

    private function sentCode(): string
    {
        $code = null;

        Mail::assertSent(VerifyEmailCode::class, function (VerifyEmailCode $mail) use (&$code) {
            $code = $mail->code;

            return strlen($mail->code) === 6;
        });

        $this->assertIsString($code);

        return $code;
    }
}
