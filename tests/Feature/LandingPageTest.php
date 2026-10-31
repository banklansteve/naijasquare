<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SignupRoute;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_guests_see_the_landing_page_without_an_invite(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('invite', null)
                ->where('headline', 'Your people. Your city. Real help.')
                ->where('emphasis', 'Real help.')
                ->where('subline', 'Moving, job-hunting, finding a GP, sorting paperwork: We connect you to verified people, trusted resources and local support - wherever you are.')
                ->where('banner.primary', 'Create A Free Account')
                ->where('banner.secondary', 'Explore Communities')
                ->where('banner.primaryHref', route(SignupRoute::NAME))
                ->where('closer', 'Ask, offer, and meet up in person.')
                ->where('close.line', 'Join the trusted space for Nigerians in the UK.')
                ->where('close.cta', 'Create Free Account')
                ->where('close.ctaHref', route(SignupRoute::NAME))
                ->where('close.account', 'Already have an account?')
                ->where('close.signIn', 'Sign in')
                ->where('close.signInHref', route('login'))
                ->has('board', 4)
                ->has('values.points', 3)
                ->where('values.heading', 'Then it feels like yours')
                ->where('values.points.0.title', 'Communities that match your life.')
                ->where('values.points.1.title', 'Real help when you need it')
                ->where('values.points.1.body', fn (string $body) => str_contains($body, 'get advice'))
                ->where('values.points.2.title', 'People & places you can trust')
                ->has('doHere.cards', 4)
                ->has('trust.promises', 4)
                ->has('how.steps', 3)
                ->where('faq.heading', 'Good Questions. Straight Answers.')
                ->where('faq.subline', 'Everything you need to know before joining.')
                ->has('faq.items', 5)
                ->missing('founder')
                ->missing('faqs')
                ->where('communities', [])
                ->where('aside.title', 'Someone here has been through it.')
                ->where('aside.body', fn (string $body) => str_contains($body, 'Ask. Offer. Meet.'))
            );

        $values = file_get_contents(resource_path('js/components/landing/ValueCards.vue'));
        $pageSource = file_get_contents(resource_path('js/pages/Landing.vue'));
        $faq = file_get_contents(resource_path('js/components/landing/FaqList.vue'));

        $this->assertIsString($values);
        $this->assertMatchesRegularExpression('/<h2 id="values-heading"/', $values);
        $this->assertMatchesRegularExpression('/<h3 class="text-xl/', $values);
        $this->assertDoesNotMatchRegularExpression('/<button\b|<a\b|<Link\b/', $values);

        $this->assertIsString($pageSource);
        $this->assertStringNotContainsString('A note from the founder', $pageSource);
        $this->assertMatchesRegularExpression('/id="close-heading"/', $pageSource);
        $this->assertDoesNotMatchRegularExpression('/id="close-heading"[^>]*font-serif/', $pageSource);
        $this->assertMatchesRegularExpression('/from-lagoon-600 via-lagoon-800 to-lagoon-900/', $pageSource);
        $this->assertStringNotContainsString('text-lagoon-200', $pageSource);
        $this->assertLessThan(
            strpos($pageSource, '<HowPath'),
            strpos($pageSource, '<TrustBoard'),
        );

        $this->assertIsString($faq);
        $this->assertMatchesRegularExpression('/<h2 id="faq-heading"/', $faq);
        $this->assertMatchesRegularExpression('/max-w-4xl/', $faq);
        $this->assertMatchesRegularExpression('/<button\b/', $faq);
    }

    public function test_landing_page_includes_link_preview_tags_for_whatsapp(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:image" content="'.url('/images/og-card.png').'"', false)
            ->assertSee('rel="manifest"', false);
    }

    public function test_trust_copy_states_email_verification_applies_to_everyone(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('trust.heading', 'Built so you can trust what you find.')
                ->where('trust.subline', 'Four clear promises.')
                ->where('trust.promises.1.title', 'Real people only')
                ->where('trust.promises.1.body', fn (string $line) => str_contains($line, 'including people who arrive with an invite'))
                ->where('trust.promises.0.notice.status', 'Reported')
                ->where('trust.promises.0.notice.meta', 'Housing · Leeds')
                ->where('trust.promises.0.body', fn (string $line) => str_contains($line, 'immigration'))
                ->missing('trust.promises.0.calls')
                ->missing('trust.promises.0.example')
                ->where('trust.promises.3.body', 'We never sell your data. You control what people see.')
                ->where('trust.promises.3.privacy.label', 'Shown as')
                ->where('trust.promises.3.privacy.level', 'City level')
                ->where('trust.promises.3.privacy.place', 'Cardiff')
                ->missing('trust.promises.3.fields')
                ->where('board.0', fn ($card) => ! isset($card['name']) && ! isset($card['author']))
            );

        $board = file_get_contents(resource_path('js/components/landing/TrustBoard.vue'));
        $face = file_get_contents(resource_path('js/components/landing/TrustFace.vue'));

        $this->assertIsString($board);
        $this->assertIsString($face);
        $this->assertMatchesRegularExpression('/<h2 id="trust-heading"/', $board);
        $this->assertMatchesRegularExpression('/<h3 class="text-xl/', $face);
        $this->assertDoesNotMatchRegularExpression('/<button\b|<a\b|<Link\b/', $face);
    }

    public function test_board_examples_are_labelled_and_have_no_live_activity(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('board.0.category', 'Housing')
                ->where('board.0.place', 'Leeds')
                ->where('board.0.kind', 'Need')
                ->where('board.0.detail', 'Needed from January')
                ->where('board.1.place', 'Manchester')
                ->where('board.2.category', 'Offer')
                ->where('board.3.category', 'Event')
                ->where('board.3.place', 'London')
                ->where('board.3.kind', 'Meet')
                ->missing('board.0.author')
                ->missing('board.0.replies')
            );
    }

    public function test_do_here_lists_four_doorways_and_the_signup_cta(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('doHere.heading', 'What people do here')
                ->where('doHere.subline', 'No feed to scroll. Just things that help.')
                ->where('doHere.cards.0.title', 'Get things sorted')
                ->where('doHere.cards.0.body', 'Housing, jobs, schools, paperwork. Ask people who\'ve already done it, or share what you know.')
                ->where('doHere.cards.0.tags', ['Rooms', 'Jobs', 'Paperwork'])
                ->where('doHere.cards.1.title', 'Find your people')
                ->where('doHere.cards.1.body', 'Join groups for your city, your profession or your stage of life. Some are open to all, and others approve each new member.')
                ->where('doHere.cards.1.tags', ['City', 'Profession', 'New arrivals'])
                ->where('doHere.cards.2.title', 'Meet in person')
                ->where('doHere.cards.2.body', 'Take it from messages to real meetups: coffee, brunch and professional mixers near you, with RSVP.')
                ->where('doHere.cards.2.tags', ['Events', 'Meetups', 'RSVP'])
                ->where('doHere.cards.3.title', 'Ask who to trust')
                ->where('doHere.cards.3.body', 'Need a plumber, tutor or accountant? Ask members who they\'d use, and hear from people who\'ve actually hired them.')
                ->where('doHere.cards.3.tags', ['Trades', 'Services', 'Advice'])
                ->where('doHere.cta', 'Create your free account')
                ->missing('doHere.note')
                ->where('doHere.signupHref', route(SignupRoute::NAME))
                ->missing('uses')
            );
    }

    public function test_how_it_works_shows_three_product_steps_and_the_signup_cta(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('how.heading', 'Get started in 3 simple steps')
                ->where('how.subline', 'A free account, the right communities, then people who can help.')
                ->where('how.steps.0.title', 'Join free')
                ->where('how.steps.0.body', 'Create your profile in minutes. Choose what interests you, and what others see.')
                ->where('how.steps.0.screen.detail', 'Area only')
                ->where('how.steps.0.screen.privacy', 'Show others')
                ->where('how.steps.0.screen.nameValue', 'You choose')
                ->where('how.steps.1.title', 'Find your communities')
                ->where('how.steps.1.body', 'Search for communities, services or resources. Join an open one, or request to join.')
                ->where('how.steps.1.screen.groups.0.access', 'Join')
                ->where('how.steps.1.screen.groups.0.open', true)
                ->where('how.steps.1.screen.groups.1.access', 'Request to join')
                ->where('how.steps.1.screen.groups.1.open', false)
                ->where('how.steps.2.title', 'Connect and grow')
                ->where('how.steps.2.body', 'Build real relationships and get the support you need.')
                ->where('how.steps.2.screen.tag', 'Support')
                ->where('how.steps.2.screen.status', 'Connected')
                ->missing('how.steps.3')
                ->where('how.cta', 'Create your free account')
                ->where('how.note', 'It\'s Free, and takes just about a minute to join.')
                ->where('how.signupHref', route(SignupRoute::NAME))
                ->missing('steps')
            );
    }

    public function test_how_it_works_is_a_path_of_headings_not_links_on_the_screens(): void
    {
        $page = file_get_contents(resource_path('js/components/landing/HowPath.vue'));
        $scene = file_get_contents(resource_path('js/components/landing/PathScene.vue'));

        $this->assertIsString($page);
        $this->assertIsString($scene);
        $this->assertMatchesRegularExpression('/<h2 id="steps-heading"/', $page);
        $this->assertStringContainsString('bg-linear-to-b from-paper via-lagoon-100 to-paper', $page);
        $this->assertMatchesRegularExpression('/<ol /', $page);
        $this->assertMatchesRegularExpression('/<h3 class="text-xl/', $page);
        $this->assertDoesNotMatchRegularExpression('/<h1\b/', $page);
        $this->assertStringContainsString('aria-hidden="true"', $scene);
        $this->assertDoesNotMatchRegularExpression('/<a\b|<Link\b/', $scene);
    }

    public function test_do_here_heading_hierarchy_is_a_section_heading_and_card_titles(): void
    {
        $page = file_get_contents(resource_path('js/pages/Landing.vue'));
        $card = file_get_contents(resource_path('js/components/landing/DoorwayCard.vue'));
        $mark = file_get_contents(resource_path('js/components/landing/DoorwayMark.vue'));

        $this->assertIsString($page);
        $this->assertIsString($card);
        $this->assertIsString($mark);
        $this->assertMatchesRegularExpression('/<h2 id="do-here-heading"/', $page);
        $this->assertMatchesRegularExpression('/<h3 class="mt-4/', $card);
        $this->assertDoesNotMatchRegularExpression('/<h1\b/', $card);
        $this->assertDoesNotMatchRegularExpression('/<a\b|<Link\b/', $card);
        $this->assertStringContainsString('aria-hidden="true"', $mark);
    }

    public function test_footer_pages_are_readable(): void
    {
        $this->get('/privacy')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Info')->where('title', 'Privacy'));
        $this->get('/guidelines')->assertOk();
        $this->get('/safety')->assertOk();
        $this->get('/contact')->assertOk();
    }

    public function test_signed_in_members_never_see_the_landing_page(): void
    {
        $this->actingAs(User::factory()->make())
            ->get('/')
            ->assertRedirect(route('home'));
    }

    public function test_signed_in_members_are_redirected_away_from_join_and_sign_in(): void
    {
        $user = User::factory()->make();

        $this->actingAs($user)->get('/join')->assertRedirect('/home');
        $this->actingAs($user)->get('/sign-in')->assertRedirect('/home');
    }

    public function test_join_shows_the_signup_form_and_sign_in_stays_a_holding_screen(): void
    {
        $this->get('/join')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Signup')
                ->where('lifeStages.new_arrival', 'New arrival (last 12 months)')
                ->where('interests.housing', 'Housing & settling in')
                ->where('interests.looking', 'Just looking around')
                ->where('privacyNote', fn (string $note) => str_contains($note, 'never share'))
            );

        $this->get('/sign-in')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/Pending')
                ->where('intent', 'sign-in')
                ->has('title')
                ->has('body')
            );

        $signup = file_get_contents(resource_path('js/pages/Auth/Signup.vue'));
        $this->assertIsString($signup);
        $this->assertStringContainsString('href="/sign-in"', $signup);
        $this->assertStringContainsString('Already have an account?', $signup);
        $this->assertStringContainsString("title: 'Create Your Account'", $signup);
        $this->assertStringContainsString("title: 'Check your details'", $signup);
        $this->assertStringNotContainsString("title: 'Agreement'", $signup);
        $this->assertStringNotContainsString("kicker: 'Right now'", $signup);
        $this->assertStringNotContainsString("kicker: 'Your photo'", $signup);
        $this->assertStringNotContainsString("kicker: 'Agreement'", $signup);
        $this->assertMatchesRegularExpression('/Edit\s*<\/button>/', $signup);
        $this->assertStringContainsString('type="checkbox"', $signup);
        $this->assertStringContainsString('I agree to the', $signup);
        $this->assertMatchesRegularExpression('/Previous\s*<\/button>/', $signup);
    }

    public function test_sample_invite_preview_is_not_available_outside_local(): void
    {
        $this->get('/preview/invite')->assertNotFound();
    }

    public function test_sample_invite_preview_names_the_inviter_and_keeps_email_verification(): void
    {
        $this->app['env'] = 'local';

        $this->get('/preview/invite')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('invite.inviter.name', 'Ada')
                ->where('invite.community.name', 'Nigerians in Leeds')
                ->where('invite.community.requires_approval', true)
                ->where('trust.promises.1.body', fn (string $line) => str_contains($line, 'including people who arrive with an invite'))
            );
    }

    public function test_join_holding_screen_tells_invitees_they_still_verify_email(): void
    {
        $this->get('/join')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Signup')
                ->where('inviteNote', fn (string $note) => str_contains($note, 'even if someone invited you'))
            );
    }
}
