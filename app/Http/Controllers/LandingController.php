<?php

namespace App\Http\Controllers;

use App\Support\SignupRoute;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function show(): Response
    {
        return $this->render(invite: null);
    }

    public function previewInvite(): Response
    {
        abort_unless(app()->isLocal(), 404);

        return $this->render(invite: config('landing.preview_invite'));
    }

    public function join(): Response
    {
        return $this->pending('join');
    }

    public function signIn(): Response
    {
        return $this->pending('sign-in');
    }

    public function page(string $page): Response
    {
        $copy = config("landing.pages.{$page}");

        abort_unless(is_array($copy), 404);

        return Inertia::render('Info', [
            'title' => $copy['title'],
            'paragraphs' => $copy['paragraphs'],
        ]);
    }

    private function pending(string $intent): Response
    {
        $copy = $intent === 'join'
            ? config('landing.pending.join')
            : config('landing.pending.sign_in');

        return Inertia::render('Account/Pending', [
            'intent' => $intent,
            'title' => $copy['title'],
            'body' => $copy['body'],
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $invite
     */
    private function render(?array $invite): Response
    {
        $meta = config('landing.meta');

        if ($invite !== null) {
            $meta['title'] = sprintf(
                '%s invited you to join %s',
                $invite['inviter']['name'],
                $invite['community']['name'],
            );
        }

        return Inertia::render('Landing', [
            'headline' => config('landing.headline'),
            'emphasis' => config('landing.emphasis'),
            'subline' => config('landing.subline'),
            'closer' => config('landing.closer'),
            'assurance' => config('landing.assurance'),
            'aside' => config('landing.aside'),
            'invite' => $invite,
            'board' => config('landing.board'),
            'doHere' => $this->doHere(),
            'communities' => config('landing.communities'),
            'steps' => config('landing.steps'),
            'trustIntro' => config('landing.trust_intro'),
            'trust' => config('landing.trust'),
            'founder' => config('landing.founder'),
            'faqs' => config('landing.faqs'),
            'footer' => config('landing.footer'),
        ])->withViewData(['meta' => $meta]);
    }

    /**
     * @return array<string, mixed>
     */
    private function doHere(): array
    {
        /** @var array<string, mixed> $section */
        $section = config('landing.do_here');

        $section['signupHref'] = route(SignupRoute::NAME);

        return $section;
    }
}
