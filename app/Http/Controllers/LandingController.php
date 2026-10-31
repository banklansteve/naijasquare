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
            'banner' => $this->banner(),
            'assurance' => config('landing.assurance'),
            'aside' => config('landing.aside'),
            'invite' => $invite,
            'board' => config('landing.board'),
            'values' => config('landing.values'),
            'doHere' => $this->doHere(),
            'communities' => config('landing.communities'),
            'how' => $this->how(),
            'trust' => config('landing.trust'),
            'faq' => config('landing.faq'),
            'close' => $this->close(),
            'footer' => config('landing.footer'),
        ])->withViewData(['meta' => $meta]);
    }

    /**
     * @return array<string, mixed>
     */
    private function banner(): array
    {
        /** @var array<string, mixed> $banner */
        $banner = config('landing.banner');

        $banner['primaryHref'] = route(SignupRoute::NAME);

        return $banner;
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

    /**
     * @return array<string, mixed>
     */
    private function close(): array
    {
        /** @var array<string, mixed> $close */
        $close = config('landing.close');

        $close['ctaHref'] = route(SignupRoute::NAME);
        $close['signInHref'] = route('login');

        return $close;
    }

    /**
     * @return array<string, mixed>
     */
    private function how(): array
    {
        /** @var array<string, mixed> $section */
        $section = config('landing.how');

        $section['signupHref'] = route(SignupRoute::NAME);

        return $section;
    }
}
