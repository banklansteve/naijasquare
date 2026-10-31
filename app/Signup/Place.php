<?php

namespace App\Signup;

final class Place
{
    public function __construct(
        public string $postcode,
        public string $city,
        public ?string $area,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}

    public function label(): string
    {
        if ($this->area === null || $this->area === '' || strcasecmp($this->area, $this->city) === 0) {
            return $this->city;
        }

        return $this->area.', '.$this->city;
    }
}
