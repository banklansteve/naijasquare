<?php

namespace App\Signup;

final class ConfirmedAddress
{
    public function __construct(
        public string $addressLine,
        public Place $place,
        public string $houseNumber,
        public ?string $street,
    ) {}

    /**
     * @return array{address: string, place: string, house_number: string, street: string|null, postcode: string, city: string}
     */
    public function lookup(): array
    {
        return [
            'address' => $this->addressLine,
            'place' => $this->place->label(),
            'house_number' => $this->houseNumber,
            'street' => $this->street,
            'postcode' => $this->place->postcode,
            'city' => $this->place->city,
        ];
    }
}
