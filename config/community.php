<?php

return [

    'access_modes' => [
        'open' => [
            'label' => 'Open',
            'description' => 'Anyone can join and see posts.',
        ],
        'request' => [
            'label' => 'Request to join',
            'description' => 'People request access, admins approve.',
        ],
        'invite_only' => [
            'label' => 'Invite only',
            'description' => 'Only invited people can join.',
        ],
    ],

    'location_types' => [
        'city' => 'City',
        'region' => 'Region',
        'uk_wide' => 'UK-wide',
    ],

    'tags' => [
        'new_arrivals' => 'New Arrivals',
        'professionals' => 'Professionals',
        'parents' => 'Parents',
        'housing' => 'Housing',
        'healthcare' => 'Healthcare',
        'ethnic_cultural' => 'Ethnic / Cultural',
        'faith' => 'Faith',
        'local_services' => 'Local services',
        'events' => 'Events',
        'students' => 'Students',
        'women' => 'Women',
        'men' => 'Men',
    ],

    'guideline_templates' => [
        [
            'label' => 'Be respectful',
            'body' => 'Be respectful. Disagree without insults. Everyone here is finding their way.',
        ],
        [
            'label' => 'No spam or scams',
            'body' => 'No spam, scams, or pressure to send money or documents. Report anything that feels off.',
        ],
        [
            'label' => 'Keep posts relevant',
            'body' => 'Keep posts relevant to this community. Off-topic chats belong elsewhere.',
        ],
        [
            'label' => 'No hate speech',
            'body' => 'No hate speech, harassment, or discrimination of any kind.',
        ],
    ],

    'who_can_post' => [
        'members' => 'All members',
        'admins' => 'Admins only',
    ],

    'member_list_visibility' => [
        'members' => 'All members',
        'admins' => 'Admins only',
    ],

];
