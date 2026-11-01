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
            'label' => 'Jobs',
            'safety_warning' => true,
        ],
        'immigration' => [
            'label' => 'Immigration',
            'safety_warning' => true,
        ],
        'healthcare' => [
            'label' => 'Healthcare',
            'safety_warning' => false,
        ],
        'everyday' => [
            'label' => 'Everyday help',
            'safety_warning' => false,
        ],
        'local_services' => [
            'label' => 'Local services',
            'safety_warning' => false,
        ],
        'other' => [
            'label' => 'Other',
            'safety_warning' => false,
        ],
    ],

    'urgencies' => [
        'low' => [
            'label' => 'Whenever',
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

];
