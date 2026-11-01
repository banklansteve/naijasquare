<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUsersSeeder extends Seeder
{
    private const EMAIL_DOMAIN = 'email.com';

    private const LEGACY_EMAIL_DOMAIN = 'naijasquare.test';

    /**
     * Local testing only. Not for production.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DummyUsersSeeder will not run in production.');

            return;
        }

        $this->backfillLegacyEmails();

        $addresses = $this->addresses();
        $byCity = $this->groupAddressesByCity($addresses);
        $people = $this->people();
        $lifeStages = array_keys(config('signup.life_stages'));
        $interestKeys = array_keys(config('signup.interests'));

        $created = [];

        foreach ($people as $index => $person) {
            $cityKey = $person['city_key'];
            $pool = $byCity[$cityKey];
            $house = $pool[$index % count($pool)];

            $interestCount = fake()->numberBetween(1, 4);
            $interests = fake()->randomElements($interestKeys, $interestCount);

            $user = User::query()->updateOrCreate(
                ['email' => $person['email']],
                [
                    'name' => $person['first'].' '.$person['last'],
                    'first_name' => $person['first'],
                    'last_name' => $person['last'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'house_number' => $house['house_number'],
                    'postcode' => $house['postcode'],
                    'address_line' => $house['address'],
                    'place_label' => $house['place'],
                    'city' => $house['city'],
                    'life_stage' => fake()->randomElement($lifeStages),
                    'interests' => array_values($interests),
                    'terms_accepted_at' => now(),
                ],
            );

            $created[$cityKey][] = $user;
        }

        $communities = $this->communities();

        foreach ($communities as $definition) {
            $locals = collect($created[$definition['city_key']] ?? []);
            $joinCount = (int) max(1, round($locals->count() * 0.8));
            $members = $locals->shuffle()->take($joinCount);
            $owner = $members->first();

            $community = Community::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'description' => $definition['description'],
                    'city' => $definition['city'],
                    'interest_keys' => $definition['interest_keys'],
                    'listed' => true,
                    'access_mode' => Community::ACCESS_REQUEST,
                    'location_type' => 'city',
                    'location_label' => $definition['city'],
                    'tag_keys' => [],
                    'created_by' => $owner?->id,
                ],
            );

            foreach ($members as $index => $member) {
                $community->members()->syncWithoutDetaching([
                    $member->id => [
                        'role' => $index === 0 ? Community::ROLE_OWNER : Community::ROLE_MEMBER,
                        'guidelines_accepted_at' => now(),
                    ],
                ]);
            }
        }

        $this->command?->info('Seeded '.count($people).' dummy users and '.count($communities).' communities.');
        $this->command?->info('Password for all dummy users: password');
        $this->command?->info('Emails use *@'.self::EMAIL_DOMAIN);
    }

    private function backfillLegacyEmails(): void
    {
        $updated = User::query()
            ->where('email', 'like', '%@'.self::LEGACY_EMAIL_DOMAIN)
            ->get();

        foreach ($updated as $user) {
            $local = strstr($user->email, '@', true);
            if ($local === false || $local === '') {
                continue;
            }

            $user->forceFill([
                'email' => $local.'@'.self::EMAIL_DOMAIN,
            ])->save();
        }

        if ($updated->isNotEmpty()) {
            $this->command?->info('Backfilled '.$updated->count().' emails from @'.self::LEGACY_EMAIL_DOMAIN.' to @'.self::EMAIL_DOMAIN);
        }
    }

    /**
     * @return list<array{first: string, last: string, email: string, city_key: string}>
     */
    private function people(): array
    {
        $names = [
            ['Ada', 'Okeke'], ['Chidi', 'Nwosu'], ['Funke', 'Adeyemi'], ['Ifeanyi', 'Okafor'],
            ['Ngozi', 'Eze'], ['Tunde', 'Balogun'], ['Amaka', 'Obi'], ['Kelechi', 'Uche'],
            ['Yemi', 'Adebayo'], ['Chiamaka', 'Okoro'], ['Emeka', 'Ibe'], ['Bisi', 'Oladipo'],
            ['Nneka', 'Chukwu'], ['Segun', 'Ajayi'], ['Halima', 'Bello'], ['Ibrahim', 'Musa'],
            ['Zainab', 'Suleiman'], ['Oluwaseun', 'Fashola'], ['Chioma', 'Madu'], ['David', 'Okoye'],
            ['Grace', 'Akinola'], ['Michael', 'Ekwueme'], ['Blessing', 'Udo'], ['Daniel', 'Onyema'],
            ['Ruth', 'Ogbonna'], ['Samuel', 'Adewale'], ['Esther', 'Nnamdi'], ['Joseph', 'Bankole'],
            ['Mary', 'Okonkwo'], ['Peter', 'Adegoke'], ['Sarah', 'Iwu'], ['James', 'Okorie'],
            ['Faith', 'Okafor'], ['Emmanuel', 'Dike'], ['Joy', 'Afolabi'], ['Cynthia', 'Nwankwo'],
            ['Victor', 'Osuji'], ['Patricia', 'Eke'], ['Anthony', 'Ojo'], ['Helen', 'Abdul'],
            ['Francis', 'Chima'], ['Deborah', 'Lawal'], ['Gabriel', 'Okeke'], ['Lilian', 'Umeh'],
            ['Benjamin', 'Adesina'], ['Rose', 'Ike'], ['Stephen', 'Ogundipe'], ['Angela', 'Nwafor'],
            ['Paul', 'Sanni'], ['Juliet', 'Ezeani'],
        ];

        $cityCycle = ['Sunderland', 'Newcastle upon Tyne', 'Manchester', 'Birmingham'];
        $people = [];

        foreach ($names as $index => [$first, $last]) {
            $cityKey = $cityCycle[$index % 4];
            $slug = strtolower($first.'.'.$last.'.'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT));
            $people[] = [
                'first' => $first,
                'last' => $last,
                'email' => $slug.'@'.self::EMAIL_DOMAIN,
                'city_key' => $cityKey,
            ];
        }

        return $people;
    }

    /**
     * @return list<array{name: string, description: string, city: string, city_key: string, interest_keys: list<string>|null}>
     */
    private function communities(): array
    {
        return [
            [
                'name' => 'Nigerians in Sunderland',
                'description' => 'A welcoming community for Nigerians living in and around Sunderland.',
                'city' => 'Sunderland',
                'city_key' => 'Sunderland',
                'interest_keys' => null,
            ],
            [
                'name' => 'Christian mothers in Sunderland',
                'description' => 'Support, prayer, and practical help for Christian mothers in Sunderland.',
                'city' => 'Sunderland',
                'city_key' => 'Sunderland',
                'interest_keys' => ['community', 'events'],
            ],
            [
                'name' => 'Nigerians in Newcastle',
                'description' => 'Connect with Nigerians across Newcastle upon Tyne.',
                'city' => 'Newcastle upon Tyne',
                'city_key' => 'Newcastle upon Tyne',
                'interest_keys' => null,
            ],
            [
                'name' => 'Health Care Workers in Newcastle',
                'description' => 'Healthcare professionals supporting each other in Newcastle.',
                'city' => 'Newcastle upon Tyne',
                'city_key' => 'Newcastle upon Tyne',
                'interest_keys' => ['work', 'networking'],
            ],
            [
                'name' => 'Nigerians in Birmingham',
                'description' => 'Community, advice, and friendship for Nigerians in Birmingham.',
                'city' => 'Birmingham',
                'city_key' => 'Birmingham',
                'interest_keys' => null,
            ],
            [
                'name' => 'Newcomers in Birmingham',
                'description' => 'For people settling into Birmingham — housing, services, and first steps.',
                'city' => 'Birmingham',
                'city_key' => 'Birmingham',
                'interest_keys' => ['housing', 'community', 'services'],
            ],
            [
                'name' => 'Newcomers in Manchester',
                'description' => 'Practical help for people new to Manchester.',
                'city' => 'Manchester',
                'city_key' => 'Manchester',
                'interest_keys' => ['housing', 'community', 'services'],
            ],
            [
                'name' => 'Professionals in Manchester',
                'description' => 'Career, networking, and peer support for professionals in Manchester.',
                'city' => 'Manchester',
                'city_key' => 'Manchester',
                'interest_keys' => ['work', 'networking'],
            ],
        ];
    }

    /**
     * @return list<array{house_number: string, street: string, address: string, city: string, place: string, postcode: string}>
     */
    private function addresses(): array
    {
        /** @var list<array{postcode: string, city: string, place: string, houses: list<array<string, string>>}> $rows */
        $rows = json_decode(
            (string) file_get_contents(database_path('data/dummy_addresses.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $overrides = [
            'SR4 0NP' => [
                ['12', 'Southside Gardens'],
                ['54', 'Southside Gardens'],
                ['10', 'Southside Gardens'],
            ],
            'SR4 6NT' => [
                ['28', 'Midmoor Road'],
                ['30', 'Midmoor Road'],
                ['32', 'Midmoor Road'],
            ],
            'SR1 1EE' => [
                ['24', 'Norfolk Street'],
                ['26', 'Norfolk Street'],
                ['1', 'Norfolk Street'],
            ],
        ];

        $flat = [];

        foreach ($rows as $row) {
            $postcode = $row['postcode'];
            $houses = $row['houses'];

            if (isset($overrides[$postcode])) {
                $houses = [];
                foreach ($overrides[$postcode] as [$number, $street]) {
                    $houses[] = [
                        'house_number' => $number,
                        'street' => $street,
                        'address' => $number.' '.$street.', '.$row['city'].', '.$postcode,
                        'city' => $row['city'],
                        'place' => $row['place'],
                        'postcode' => $postcode,
                    ];
                }
            }

            foreach ($houses as $house) {
                $flat[] = $house;
            }
        }

        return $flat;
    }

    /**
     * @param  list<array{house_number: string, street: string, address: string, city: string, place: string, postcode: string}>  $addresses
     * @return array<string, list<array{house_number: string, street: string, address: string, city: string, place: string, postcode: string}>>
     */
    private function groupAddressesByCity(array $addresses): array
    {
        $grouped = [];

        foreach ($addresses as $address) {
            $grouped[$address['city']][] = $address;
        }

        return $grouped;
    }
}
