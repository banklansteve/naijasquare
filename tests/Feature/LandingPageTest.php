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
                ->where('headline', 'Practical help, from people like you.')
                ->where('subline', 'Moving, job-hunting, finding a GP, sorting paperwork: get real answers from Nigerians who\'ve been where you are, and help others when you can.')
                ->where('closer', 'Ask, offer, and meet up in person.')
                ->has('board', 4)
                ->has('doHere.cards', 4)
                ->has('trust', 4)
                ->has('steps', 3)
                ->has('faqs', 5)
                ->where('communities', [])
                ->where('aside.title', 'Someone here has been through it.')
                ->where('aside.body', fn (string $body) => str_contains($body, 'Ask. Offer. Meet.'))
            );
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
                ->where('trust.0.body', fn (string $line) => str_contains($line, 'including people who arrive with an invite'))
                ->where('board.0', fn ($card) => ! isset($card['name']) && ! isset($card['author']))
            );
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
                ->where('doHere.note', 'Free. Takes a minute. You choose which communities to join.')
                ->where('doHere.signupHref', route(SignupRoute::NAME))
                ->missing('uses')
            );
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
            ->assertRedirect('/home');
    }

    public function test_signed_in_members_are_redirected_away_from_join_and_sign_in(): void
    {
        $user = User::factory()->make();

        $this->actingAs($user)->get('/join')->assertRedirect('/home');
        $this->actingAs($user)->get('/sign-in')->assertRedirect('/home');
    }

    public function test_join_and_sign_in_show_a_holding_screen_until_accounts_exist(): void
    {
        $this->get('/join')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/Pending')
                ->where('intent', 'join')
                ->has('title')
                ->has('body')
            );

        $this->get('/sign-in')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/Pending')
                ->where('intent', 'sign-in')
                ->has('title')
                ->has('body')
            );
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
                ->where('trust.0.body', fn (string $line) => str_contains($line, 'including people who arrive with an invite'))
            );
    }

    public function test_join_holding_screen_tells_invitees_they_still_verify_email(): void
    {
        $this->get('/join')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/Pending')
                ->where('body', fn (string $body) => str_contains($body, 'even if someone invited you'))
            );
    }
}
