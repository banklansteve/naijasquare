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
| landing page hides that section when the list is empty.
|
| Set `founder.name` to the founder's real name. Do not invent one.
|
*/

return [

    'headline' => 'Practical help, from people like you.',

    'emphasis' => 'from people like you.',

    'subline' => 'Moving, job-hunting, finding a GP, sorting paperwork: get real answers from Nigerians who\'ve been where you are, and help others when you can.',

    'closer' => 'Ask, offer, and meet up in person.',

    'assurance' => 'Free. Takes a minute. You choose which communities to join.',

    'aside' => [
        'title' => 'Someone here has been through it.',
        'body' => 'Real help from Nigerians who\'ve been where you are. Ask. Offer. Meet.',
        'image' => '/images/banner-gathering.jpg',
    ],

    'meta' => [
        'title' => 'Practical help, from people like you.',
        'description' => 'Moving, job-hunting, finding a GP, sorting paperwork: get real answers from Nigerians who\'ve been where you are, and help others when you can. Ask, offer, and meet up in person.',
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
        'note' => 'Free. Takes a minute. You choose which communities to join.',
    ],

    /*
    | Real public communities, once they exist. An empty list hides the
    | "Find your community" section.
    */
    'communities' => [],

    'steps' => [
        [
            'icon' => 'user',
            'title' => 'Create your account',
            'body' => 'A minute to open it. You verify your email, then choose the name people will see.',
        ],
        [
            'icon' => 'users',
            'title' => 'Join what fits you',
            'body' => 'Pick communities for your city, your work, or the stage of life you are in.',
        ],
        [
            'icon' => 'pin',
            'title' => 'Ask, offer, or show up',
            'body' => 'Post a need, offer help, or come to an event. The conversation stays on the post.',
        ],
    ],

    'trust_intro' => 'A few promises, written so you can read them before you join.',

    'trust' => [
        [
            'icon' => 'verified',
            'title' => 'Real people only',
            'body' => 'Everyone verifies their email, including people who arrive with an invite.',
        ],
        [
            'icon' => 'users',
            'title' => 'Communities choose',
            'body' => 'Each community decides who can join. Some are open. Others approve each new member.',
        ],
        [
            'icon' => 'shield',
            'title' => 'Scams are called out',
            'body' => 'Housing, job and immigration posts carry scam warnings.',
        ],
        [
            'icon' => 'lock',
            'title' => 'Your details stay yours',
            'body' => 'We never sell your data. You choose your name and how precisely your location shows.',
        ],
    ],

    'founder' => [
        'name' => null,
        'paragraphs' => [
            'I kept watching useful help vanish inside group chats. Someone would explain a process, warn people about a scam, or point to a person who could actually help, and a week later it was gone.',
            'NaijaSquare is the noticeboard I wanted instead. A place that stays. You can see who asked you to come. Each community decides who walks in.',
            'It is for getting things sorted with people who already understand the shape of your week, then meeting them in real life when you want to.',
        ],
    ],

    'faqs' => [
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
