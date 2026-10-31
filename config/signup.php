<?php

/*
| Signup choices live here so a new country can change the words
| without a code change. The postcode lookup is UK postcodes.io for the
| public area and city (D-030). The house itself is confirmed with
| Ideal Postcodes (D-031).
*/

return [

    'code_ttl_minutes' => 10,

    'code_max_attempts' => 5,

    'privacy_note' => 'We never share your exact address. People only see your area and city.',

    'invite_note' => 'You still verify your email before you can join a community, even if someone invited you.',

    'life_stages' => [
        'new_arrival' => 'New arrival (last 12 months)',
        'few_years' => 'Been here a few years',
        'settled' => 'Long-term resident / settled',
        'exploring' => 'Just exploring',
    ],

    'interests' => [
        'community' => 'Finding community',
        'housing' => 'Housing & settling in',
        'work' => 'Jobs & career',
        'services' => 'Local services & recommendations',
        'events' => 'Events & meetups',
        'networking' => 'Professional networking',
        'looking' => 'Just looking around',
    ],

];
