<?php

namespace App\Signup;

use Illuminate\Support\Facades\Http;

class PostcodeDirectory
{
    public function find(string $postcode): Place
    {
        $normalised = $this->normalise($postcode);

        if ($normalised === null) {
            throw new UnknownPostcode('That postcode does not look right.');
        }

        $response = Http::timeout(5)
            ->acceptJson()
            ->get('https://api.postcodes.io/postcodes/'.rawurlencode(str_replace(' ', '', $normalised)));

        if ($response->status() === 404) {
            throw new UnknownPostcode('We could not find that postcode.');
        }

        if ($response->failed() || $response->json('status') !== 200) {
            throw new PostcodeLookupFailed('The postcode service did not answer.');
        }

        /** @var array<string, mixed> $result */
        $result = $response->json('result') ?? [];
        $city = trim((string) ($result['admin_district'] ?? ''));

        if ($city === '') {
            throw new UnknownPostcode('We could not find that postcode.');
        }

        return new Place(
            postcode: (string) ($result['postcode'] ?? $normalised),
            city: $city,
            area: $this->area($result, $city),
            latitude: is_numeric($result['latitude'] ?? null) ? (float) $result['latitude'] : null,
            longitude: is_numeric($result['longitude'] ?? null) ? (float) $result['longitude'] : null,
        );
    }

    private function normalise(string $postcode): ?string
    {
        $compact = strtoupper(preg_replace('/\s+/', '', trim($postcode)) ?? '');

        if (! preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\d[A-Z]{2}$/', $compact)) {
            return null;
        }

        return substr($compact, 0, -3).' '.substr($compact, -3);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function area(array $result, string $city): ?string
    {
        $parish = trim((string) ($result['parish'] ?? ''));

        if ($this->isLocality($parish, $city)) {
            return $parish;
        }

        $ward = trim((string) ($result['admin_ward'] ?? ''));

        if ($this->isLocality($ward, $city)) {
            return $ward;
        }

        return null;
    }

    private function isLocality(string $value, string $city): bool
    {
        if ($value === '' || strcasecmp($value, $city) === 0) {
            return false;
        }

        return ! str_contains(strtolower($value), 'unparished');
    }
}
