<?php

namespace App\Signup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PremiseDirectory
{
    public function __construct(private PostcodeDirectory $postcodes) {}

    public function confirm(string $houseNumber, string $postcode): ConfirmedAddress
    {
        $place = $this->postcodes->find($postcode);
        $authoritative = true;

        try {
            $rows = $this->licensedPremises($place->postcode);
        } catch (PostcodeLookupFailed) {
            $authoritative = false;

            try {
                $rows = $this->openPremises($houseNumber, $place->postcode);
            } catch (PostcodeLookupFailed) {
                $rows = [];
            }
        }

        $matches = $this->unique($this->matching($rows, $houseNumber));

        if ($matches === []) {
            if (! $authoritative) {
                $street = $this->streetAt($place);

                if ($street !== null) {
                    return $this->confirmed(
                        trim($houseNumber).' '.$street.', '.$place->city.', '.$place->postcode,
                        $place,
                        $houseNumber,
                        $street,
                    );
                }

                return $this->confirmed($this->areaLine($houseNumber, $place), $place, $houseNumber, null);
            }

            throw new UnknownPremise('We could not find that house number at this postcode.');
        }

        if (count($matches) > 1) {
            throw new UnknownPremise('That number is shared by more than one address. Include the flat or building name.');
        }

        return $this->confirmed(
            $this->line($matches[0]),
            $place,
            $houseNumber,
            $this->streetFrom($matches[0]),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function matching(array $rows, string $houseNumber): array
    {
        return array_values(array_filter(
            $rows,
            fn (array $row): bool => $this->matches($row, $houseNumber),
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function unique(array $rows): array
    {
        $seen = [];
        $unique = [];

        foreach ($rows as $row) {
            $line = $this->line($row);

            if (isset($seen[$line])) {
                continue;
            }

            $seen[$line] = true;
            $unique[] = $row;
        }

        return $unique;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function licensedPremises(string $postcode): array
    {
        $key = (string) config('services.ideal_postcodes.key');

        if ($key === '') {
            Log::warning('Ideal Postcodes key is missing.');

            throw new PostcodeLookupFailed('The address service did not answer.');
        }

        $rows = [];
        $page = 0;

        do {
            $response = Http::withToken($key)
                ->timeout(8)
                ->acceptJson()
                ->get('https://api.ideal-postcodes.co.uk/v1/postcodes/'.rawurlencode(str_replace(' ', '', $postcode)), [
                    'page' => $page,
                    'filter' => 'line_1,line_2,line_3,post_town,postcode,building_number,building_name,sub_building_name,premise,thoroughfare,dependant_thoroughfare',
                ]);

            if ($response->failed() || $response->json('code') !== 2000) {
                Log::warning('Premise lookup unavailable.', ['status' => $response->status()]);

                throw new PostcodeLookupFailed('The address service did not answer.');
            }

            /** @var list<array<string, mixed>> $batch */
            $batch = $response->json('result') ?? [];
            $rows = array_merge($rows, $batch);
            $total = (int) $response->json('total');
            $page++;
        } while ($batch !== [] && count($rows) < $total && $page < 5);

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function openPremises(string $houseNumber, string $postcode): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'NaijaSquare (signup address check)',
        ])->timeout(8)->acceptJson()->get('https://photon.komoot.io/api/', [
            'q' => trim($houseNumber.' '.$postcode),
            'limit' => 5,
        ]);

        if ($response->failed()) {
            throw new PostcodeLookupFailed('The address service did not answer.');
        }

        $wanted = $this->compactPostcode($postcode);
        $rows = [];

        foreach ($response->json('features') ?? [] as $feature) {
            if (! is_array($feature)) {
                continue;
            }

            /** @var array<string, mixed> $properties */
            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $number = trim((string) ($properties['housenumber'] ?? ''));
            $street = trim((string) ($properties['street'] ?? ''));
            $found = $this->compactPostcode((string) ($properties['postcode'] ?? ''));

            if ($number === '' || $street === '' || $found !== $wanted) {
                continue;
            }

            $rows[] = [
                'building_number' => $number,
                'building_name' => '',
                'sub_building_name' => '',
                'premise' => '',
                'thoroughfare' => $street,
                'line_1' => trim($number.' '.$street),
                'line_2' => '',
                'line_3' => '',
                'post_town' => trim((string) ($properties['city'] ?? '')),
                'postcode' => (string) ($properties['postcode'] ?? $postcode),
            ];
        }

        return $rows;
    }

    private function confirmed(string $addressLine, Place $place, string $houseNumber, ?string $street): ConfirmedAddress
    {
        $street = $street === null ? null : trim($street);

        return new ConfirmedAddress(
            $addressLine,
            $place,
            trim($houseNumber),
            $street === '' ? null : $street,
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function streetFrom(array $row): ?string
    {
        $named = trim(trim((string) ($row['dependant_thoroughfare'] ?? '')).' '.trim((string) ($row['thoroughfare'] ?? '')));

        if ($named !== '') {
            return $named;
        }

        $line = trim((string) ($row['line_1'] ?? ''));

        foreach (['sub_building_name', 'building_name', 'building_number'] as $key) {
            $line = $this->stripLeading($line, (string) ($row[$key] ?? ''));
        }

        return $line !== '' ? $line : null;
    }

    private function stripLeading(string $line, string $prefix): string
    {
        $prefix = trim($prefix);

        if ($prefix === '' || $line === '') {
            return $line;
        }

        if (strcasecmp($line, $prefix) === 0) {
            return '';
        }

        $stripped = preg_replace('/^'.preg_quote($prefix, '/').'\b[\s,]*/i', '', $line, 1);

        return trim(is_string($stripped) ? $stripped : $line);
    }

    private function streetAt(Place $place): ?string
    {
        if ($place->latitude === null || $place->longitude === null) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'NaijaSquare (signup address check)',
            ])->timeout(8)->acceptJson()->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $place->latitude,
                'lon' => $place->longitude,
                'format' => 'jsonv2',
                'zoom' => 18,
                'addressdetails' => 1,
            ]);
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed() || ! is_array($response->json())) {
            return null;
        }

        /** @var array<string, mixed> $result */
        $result = $response->json();
        $road = trim((string) data_get($result, 'address.road'));

        if ($road === '' || ! $this->streetIsHere($place, $result)) {
            return null;
        }

        return $road;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function streetIsHere(Place $place, array $result): bool
    {
        $found = $this->compactPostcode((string) data_get($result, 'address.postcode'));

        if ($found !== '' && $found === $this->compactPostcode($place->postcode)) {
            return true;
        }

        $latitude = $result['lat'] ?? null;
        $longitude = $result['lon'] ?? null;

        if (! is_numeric($latitude) || ! is_numeric($longitude) || $place->latitude === null || $place->longitude === null) {
            return false;
        }

        return $this->metresApart($place->latitude, $place->longitude, (float) $latitude, (float) $longitude) <= 80;
    }

    private function metresApart(float $fromLat, float $fromLon, float $toLat, float $toLon): float
    {
        $latDelta = deg2rad($toLat - $fromLat);
        $lonDelta = deg2rad($toLon - $fromLon);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lonDelta / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function areaLine(string $houseNumber, Place $place): string
    {
        return trim($houseNumber).', '.$place->label().', '.$place->postcode;
    }

    private function compactPostcode(string $postcode): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($postcode)) ?? '');
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function matches(array $row, string $houseNumber): bool
    {
        $needle = $this->token($houseNumber);

        if ($needle === '') {
            return false;
        }

        $number = $this->token((string) ($row['building_number'] ?? ''));
        $name = $this->token((string) ($row['building_name'] ?? ''));
        $sub = $this->token((string) ($row['sub_building_name'] ?? ''));
        $premise = $this->token((string) ($row['premise'] ?? ''));
        $combined = $this->token(trim($sub.' '.$number));

        return in_array($needle, array_filter([$number, $name, $sub, $premise, $combined]), true);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function line(array $row): string
    {
        $parts = [];

        foreach (['line_1', 'line_2', 'line_3', 'post_town', 'postcode'] as $key) {
            $value = trim((string) ($row[$key] ?? ''));

            if ($value !== '' && ! in_array($value, $parts, true)) {
                $parts[] = $value;
            }
        }

        return implode(', ', $parts);
    }

    private function token(string $value): string
    {
        $value = strtoupper(trim($value));

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }
}
