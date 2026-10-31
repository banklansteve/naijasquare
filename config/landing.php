<?php

/*
|--------------------------------------------------------------------------
| Public landing page content
|--------------------------------------------------------------------------
|
| Copy lives here so a new country or launch region can change the words
| without a code change. Sample board cards are examples only. They
| carry no names, times, or counts.
|
| `communities` stays empty until real public communities exist. The
| landing page hides that section when the list is empty. Until then,
| Explore Communities scrolls to the community step in How it works.
|
*/

return [

    'headline' => 'Your people. Your city. Real help.',

    'emphasis' => 'Real help.',

    'subline' => 'Moving, job-hunting, finding a GP, sorting paperwork: We connect you to verified people, trusted resources and local support - wherever you are.',

    'banner' => [
        'primary' => 'Create A Free Account',
        'secondary' => 'Explore Communities',
    ],

    'values' => [
        'heading' => 'Then it feels like yours',
        'subline' => 'People at your stage of life, help when you ask, and a space that stays respectful.',
        'points' => [
            [
                'icon' => 'match',
                'title' => 'Communities that match your life.',
                'body' => 'Connect with people who share your values, interests and stage of life.',
            ],
            [
                'icon' => 'help',
                'title' => 'Real help when you need it',
                'body' => 'Ask questions, get advice and find practical support from people who understand.',
            ],
            [
                'icon' => 'trust',
                'title' => 'People & places you can trust',
                'body' => 'Verified members and community moderation keep our space safe and respectful.',
            ],
        ],
    ],

    'closer' => 'Ask, offer, and meet up in person.',

    'close' => [
        'line' => 'Join the trusted space for Nigerians in the UK.',
        'cta' => 'Create Free Account',
        'account' => 'Already have an account?',
        'signIn' => 'Sign in',
    ],

    'assurance' => 'Free. Takes a minute. You choose which communities to join.',

    'aside' => [
        'title' => 'Someone here has been through it.',
        'body' => 'Real help from Nigerians who\'ve been where you are. Ask. Offer. Meet.',
        'image' => '/images/banner-gathering.jpg',
    ],

    'meta' => [
        'title' => 'Your people. Your city. Real help.',
        'description' => 'Moving, job-hunting, finding a GP, sorting paperwork: We connect you to verified people, trusted resources and local support - wherever you are.',
        'image' => '/images/og-card.png',
    ],

    'board' => [
        [
            'category' => 'Housing',
            'place' => 'Leeds',
            'kind' => 'Need',
            'line' => 'Looking for a room near the station from January. Working full-time, non-smoker. Any areas you\'d recommend?',
            'detail' => 'Needed from January',
        ],
        [
            'category' => 'Advice',
            'place' => 'Manchester',
            'kind' => 'Need',
            'line' => 'Just moved here. Which GP surgeries are taking new patients, and how did you get registered?',
            'detail' => 'Open to answers',
        ],
        [
            'category' => 'Offer',
            'place' => 'Birmingham',
            'kind' => 'Offer',
            'line' => 'Ten years in recruitment. Happy to proofread CVs and cover letters for new arrivals, free.',
            'detail' => 'Free · Online',
        ],
        [
            'category' => 'Event',
            'place' => 'London',
            'kind' => 'Meet',
            'line' => 'Sunday brunch and board games in Peckham. Newcomers and old hands welcome. Small group, RSVP for the address.',
            'detail' => 'Sun 11am · RSVP',
        ],
    ],

    'do_here' => [
        'heading' => 'What people do here',
        'subline' => 'No feed to scroll. Just things that help.',
        'cards' => [
            [
                'illustration' => 'table',
                'title' => 'Get things sorted',
                'body' => 'Housing, jobs, schools, paperwork. Ask people who\'ve already done it, or share what you know.',
                'tags' => ['Rooms', 'Jobs', 'Paperwork'],
            ],
            [
                'illustration' => 'doorway',
                'title' => 'Find your people',
                'body' => 'Join groups for your city, your profession or your stage of life. Some are open to all, and others approve each new member.',
                'tags' => ['City', 'Profession', 'New arrivals'],
            ],
            [
                'illustration' => 'meal',
                'title' => 'Meet in person',
                'body' => 'Take it from messages to real meetups: coffee, brunch and professional mixers near you, with RSVP.',
                'tags' => ['Events', 'Meetups', 'RSVP'],
            ],
            [
                'illustration' => 'handshake',
                'title' => 'Ask who to trust',
                'body' => 'Need a plumber, tutor or accountant? Ask members who they\'d use, and hear from people who\'ve actually hired them.',
                'tags' => ['Trades', 'Services', 'Advice'],
            ],
        ],
        'cta' => 'Create your free account',
    ],

    /*
    | Real public communities, once they exist. An empty list hides the
    | "Find your community" section.
    */
    'communities' => [],

    'how' => [
        'heading' => 'Get started in 3 simple steps',
        'subline' => 'A free account, the right communities, then people who can help.',
        'steps' => [
            [
                'title' => 'Join free',
                'body' => 'Create your profile in minutes. Choose what interests you, and what others see.',
                'screen' => [
                    'type' => 'profile',
                    'label' => 'Your profile',
                    'nameLabel' => 'Name you show',
                    'nameValue' => 'You choose',
                    'interests' => [
                        ['name' => 'Housing', 'selected' => true],
                        ['name' => 'Work', 'selected' => true],
                        ['name' => 'Settling in', 'selected' => false],
                    ],
                    'privacy' => 'Show others',
                    'detail' => 'Area only',
                ],
            ],
            [
                'title' => 'Find your communities',
                'body' => 'Search for communities, services or resources. Join an open one, or request to join.',
                'screen' => [
                    'type' => 'communities',
                    'query' => 'Communities, services, resources',
                    'groups' => [
                        ['name' => 'Nigerians in Leeds', 'access' => 'Join', 'open' => true],
                        ['name' => 'New arrivals in Leeds', 'access' => 'Request to join', 'open' => false],
                    ],
                ],
            ],
            [
                'title' => 'Connect and grow',
                'body' => 'Build real relationships and get the support you need.',
                'screen' => [
                    'type' => 'thread',
                    'tag' => 'Support',
                    'line' => 'Which GP surgeries are taking new patients?',
                    'replyLabel' => 'Reply',
                    'reply' => 'Ours is. I can walk you through how I registered.',
                    'status' => 'Connected',
                ],
            ],
        ],
        'cta' => 'Create your free account',
        'note' => 'It\'s Free, and takes just about a minute to join.',
    ],

    'trust' => [
        'heading' => 'Built so you can trust what you find.',
        'subline' => 'Four clear promises.',
        'promises' => [
            [
                'title' => 'Scams get called out',
                'body' => 'Housing, job and immigration posts carry a clear warning, and anyone can report a post in two taps.',
                'notice' => [
                    'line' => 'Room available in Headingley. Quiet street, close to the shops.',
                    'meta' => 'Housing · Leeds',
                    'status' => 'Reported',
                ],
            ],
            [
                'title' => 'Real people only',
                'body' => 'Every member confirms their email before they can post or join, including people who arrive with an invite.',
                'badge' => 'Email verified',
            ],
            [
                'title' => 'Communities choose',
                'body' => 'Each community decides who joins and keeps its own guidelines.',
                'modes' => ['Open', 'Request to join', 'Invite only'],
            ],
            [
                'title' => 'Your details stay yours',
                'body' => 'We never sell your data. You control what people see.',
                'privacy' => [
                    'label' => 'Shown as',
                    'level' => 'City level',
                    'place' => 'Cardiff',
                ],
            ],
        ],
    ],

    'faq' => [
        'heading' => 'Good Questions. Straight Answers.',
        'subline' => 'Everything you need to know before joining.',
        'items' => [
            [
                'question' => 'Who is it for?',
                'answer' => 'Nigerians living away from home, aged 18 and over. You join the communities that fit your city, your work, or the stage of life you are in.',
            ],
            [
                'question' => 'Is it free?',
                'answer' => 'Yes. Creating an account is free, and the platform never takes a payment between members.',
            ],
            [
                'question' => 'Who can see my details?',
                'answer' => 'You choose the name people see. Your location stays coarse. What you post is visible in the community you posted it to.',
            ],
            [
                'question' => 'How do you deal with scams?',
                'answer' => 'Housing, job and immigration posts carry a warning. Anyone can report a post, and we do not handle money between members.',
            ],
            [
                'question' => 'Do I have to leave my WhatsApp groups?',
                'answer' => 'No. Keep the groups you already use. When something here is worth sharing, you can send the link straight into WhatsApp.',
            ],
        ],
    ],

    'footer' => [
        ['label' => 'Privacy', 'href' => '/privacy'],
        ['label' => 'Community Guidelines', 'href' => '/guidelines'],
        ['label' => 'Safety', 'href' => '/safety'],
        ['label' => 'Contact', 'href' => '/contact'],
    ],

    'pages' => [
        'privacy' => [
            'title' => 'Privacy',
            'paragraphs' => [
                'We do not sell your data. You choose the name other people see, and your location stays coarse unless you decide otherwise.',
                'A full privacy notice will be published here before the first members join.',
            ],
        ],
        'guidelines' => [
            'title' => 'Community Guidelines',
            'paragraphs' => [
                'Be useful and decent. Help where you can, and leave the next person with something they can still find.',
                'No scams, no harassment, and nothing that singles people out for who they are. Every post can be reported.',
            ],
        ],
        'safety' => [
            'title' => 'Safety',
            'paragraphs' => [
                'We never handle money between members. Housing, job and immigration posts carry an extra warning.',
                'If something feels wrong, report it from the post. Meet people in public places, and tell someone you trust where you are going.',
            ],
        ],
        'contact' => [
            'title' => 'Contact',
            'paragraphs' => [
                'A contact address will be published here before the first real members join.',
            ],
        ],
    ],

    'pending' => [
        'join' => [
            'title' => 'Accounts are almost ready',
            'body' => 'We are finishing the sign-up flow. When it opens, you will still verify your email before you can join a community, even if someone invited you.',
        ],
        'sign_in' => [
            'title' => 'Log in is almost ready',
            'body' => 'We are finishing secure sign-in. Please check back soon.',
        ],
    ],

    'preview_invite' => [
        'inviter' => [
            'name' => 'Ada',
            'member_since' => '2026',
            'email_verified' => true,
        ],
        'community' => [
            'name' => 'Nigerians in Leeds',
            'area' => 'Leeds',
            'requires_approval' => true,
        ],
        'note' => null,
    ],

];
