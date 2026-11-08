<?php

return [

    'types' => [
        'need' => 'Need',
        'offer' => 'Offer',
    ],

    'categories' => [
        'housing' => [
            'label' => 'Housing',
            'safety_warning' => true,
        ],
        'jobs' => [
            'label' => 'Jobs and careers',
            'safety_warning' => true,
        ],
        'immigration' => [
            'label' => 'Immigration and paperwork',
            'safety_warning' => true,
        ],
        'money' => [
            'label' => 'Money and banking',
            'safety_warning' => true,
        ],
        'education' => [
            'label' => 'Education and schools',
            'safety_warning' => false,
        ],
        'healthcare' => [
            'label' => 'Health and wellbeing',
            'safety_warning' => false,
        ],
        'childcare' => [
            'label' => 'Childcare and family',
            'safety_warning' => false,
        ],
        'local_services' => [
            'label' => 'Local services and trades',
            'safety_warning' => false,
        ],
        'travel_shipping' => [
            'label' => 'Travel, shipping and sending home',
            'safety_warning' => true,
        ],
        'everyday' => [
            'label' => 'Everyday help',
            'safety_warning' => false,
        ],
        'food_culture' => [
            'label' => 'Food and culture',
            'safety_warning' => false,
        ],
        'business' => [
            'label' => 'Business and side projects',
            'safety_warning' => true,
        ],
        'faith' => [
            'label' => 'Faith and fellowship',
            'safety_warning' => false,
        ],
        'training' => [
            'label' => 'Training and skills',
            'safety_warning' => false,
        ],
        'mentoring' => [
            'label' => 'Mentoring',
            'safety_warning' => false,
        ],
        'career' => [
            'label' => 'Career guidance',
            'safety_warning' => false,
        ],
        'language' => [
            'label' => 'Language learning',
            'safety_warning' => false,
        ],
        'transport' => [
            'label' => 'Transport and lifts',
            'safety_warning' => false,
        ],
        'legal' => [
            'label' => 'Legal help',
            'safety_warning' => true,
        ],
        'social' => [
            'label' => 'Social and events',
            'safety_warning' => false,
        ],
        'volunteering' => [
            'label' => 'Volunteering',
            'safety_warning' => false,
        ],
        'other' => [
            'label' => 'Other',
            'safety_warning' => false,
        ],
    ],

    'urgencies' => [
        'low' => [
            'label' => 'Normal',
            'default_days' => 30,
        ],
        'normal' => [
            'label' => 'Soon',
            'default_days' => 14,
        ],
        'urgent' => [
            'label' => 'Urgent',
            'default_days' => 7,
        ],
    ],

    'report_reasons' => [
        'scam' => 'Looks like a scam or money request',
        'harassment' => 'Harassment or hate',
        'spam' => 'Spam or irrelevant',
        'unsafe' => 'Unsafe or misleading advice',
        'other' => 'Something else',
    ],

    'safety_notice' => 'Never send money, documents, or personal details to someone you have not met in a trusted setting. Housing, job, and immigration posts are a common target for scams. Meet in public places when you meet offline.',

    /*
    | Coarse place suggestions for the Area field. Prefixed with the member's
    | city and community place at runtime — these are extras for autocomplete.
    */
    'location_suggestions' => [
        'London',
        'Manchester',
        'Birmingham',
        'Leeds',
        'Glasgow',
        'Liverpool',
        'Bristol',
        'Sheffield',
        'Edinburgh',
        'Newcastle',
        'Sunderland',
        'Cardiff',
        'Nottingham',
        'Leicester',
        'Coventry',
        'Bradford',
        'Milton Keynes',
        'Reading',
        'Croydon',
        'Luton',
    ],

];
