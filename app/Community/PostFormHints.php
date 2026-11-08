<?php

namespace App\Community;

use App\Models\Community;
use App\Models\Post;
use Illuminate\Support\Str;

class PostFormHints
{
    /**
     * @return array{
     *     title: string,
     *     body: string,
     *     place: string,
     *     topic: string,
     *     suggested_category_key: string
     * }
     */
    public function for(Community $community, string $type, ?string $categoryKey = null, ?string $userCity = null): array
    {
        $place = $this->place($community, $userCity);
        $topic = $this->topic($community);
        $categoryKey = $this->resolveCategory($community, $categoryKey, $topic);
        $categoryLabel = (string) config("posts.categories.{$categoryKey}.label", 'help');

        return [
            'title' => $this->titlePlaceholder($type, $categoryKey, $categoryLabel, $topic, $place),
            'body' => $this->bodyPlaceholder($type, $categoryKey, $place),
            'place' => $place,
            'topic' => $topic,
            'suggested_category_key' => $categoryKey,
        ];
    }

    /**
     * @return list<string>
     */
    public function locationSuggestions(Community $community, ?string $userCity = null): array
    {
        $configured = config('posts.location_suggestions', []);
        $configured = is_array($configured) ? $configured : [];

        $candidates = array_filter([
            $userCity,
            $community->city,
            $community->location_label,
            ...$configured,
        ], fn ($value): bool => is_string($value) && trim($value) !== '');

        $unique = [];
        foreach ($candidates as $candidate) {
            $label = trim((string) $candidate);
            $key = Str::lower($label);
            if (! isset($unique[$key])) {
                $unique[$key] = $label;
            }
        }

        return array_values($unique);
    }

    private function place(Community $community, ?string $userCity): string
    {
        if (is_string($community->city) && $community->city !== '') {
            return $community->city;
        }

        $fromName = $this->extractPlaceFromName($community->name);
        if ($fromName !== null) {
            return $fromName;
        }

        if (is_string($community->location_label) && $community->location_label !== '') {
            return $community->location_label;
        }

        if (is_string($userCity) && $userCity !== '') {
            return $userCity;
        }

        return 'your area';
    }

    private function topic(Community $community): string
    {
        $name = trim($community->name);
        $place = $this->extractPlaceFromName($name);

        $working = $name;
        if ($place !== null) {
            $working = (string) preg_replace('/\b(in|around|near)\s+'.preg_quote($place, '/').'\b/iu', '', $working);
            $working = str_ireplace($place, '', $working);
        }

        $working = (string) preg_replace(
            '/\b(community|circle|group|network|association|club|forum|hub|society|chapter)\b/iu',
            '',
            $working,
        );
        $working = trim((string) preg_replace('/\s+/', ' ', $working));
        $working = trim($working, " \t\n\r\0\x0B-–—,");

        if ($working === '' || Str::lower($working) === 'nigerians' || Str::lower($working) === 'nigerian') {
            return 'local help';
        }

        // Prefer a short singular-ish topic phrase.
        $words = preg_split('/\s+/', $working) ?: [];
        $words = array_slice(array_values(array_filter($words)), 0, 3);
        $topic = implode(' ', $words);

        return $topic !== '' ? Str::lower($topic) : 'local help';
    }

    private function extractPlaceFromName(string $name): ?string
    {
        if (preg_match('/\b(?:in|around|near)\s+([A-Za-z][A-Za-z\s\'-]{1,40})$/u', trim($name), $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    private function resolveCategory(Community $community, ?string $categoryKey, string $topic): string
    {
        $keys = array_keys(config('posts.categories', []));

        if (is_string($categoryKey) && in_array($categoryKey, $keys, true)) {
            return $categoryKey;
        }

        $tags = $community->cardTagKeys();
        foreach ($tags as $tag) {
            if (in_array($tag, $keys, true)) {
                return $tag;
            }
        }

        $topicLower = Str::lower($topic);
        $guesses = [
            'mentoring' => ['mentor', 'mentoring', 'coach', 'coaching'],
            'training' => ['train', 'training', 'skill', 'apprentice', 'upskill'],
            'career' => ['cv', 'interview', 'promotion'],
            'language' => ['english', 'language', 'ielts', 'esol'],
            'business' => ['business', 'side hustle', 'startup', 'enterprise'],
            'food_culture' => ['food', 'cook', 'recipe', 'culture', 'restaurant'],
            'travel_shipping' => ['shipping', 'cargo', 'remittance', 'send home', 'travel', 'flight'],
            'jobs' => ['job', 'career', 'dentist', 'nurse', 'doctor', 'work', 'professional', 'nhs'],
            'housing' => ['hous', 'flat', 'rent', 'room', 'landlord'],
            'healthcare' => ['health', 'wellbeing', 'gp', 'clinic', 'hospital', 'dental', 'dentist', 'medic'],
            'education' => ['school', 'student', 'uni', 'college', 'course'],
            'childcare' => ['parent', 'child', 'mum', 'dad', 'family'],
            'immigration' => ['visa', 'home office', 'immigration', 'settlement', 'paperwork'],
            'money' => ['bank', 'money', 'finance', 'credit'],
            'faith' => ['church', 'mosque', 'faith', 'fellowship', 'christian', 'muslim'],
            'social' => ['event', 'meetup', 'social'],
            'local_services' => ['service', 'council', 'trade', 'plumber', 'electrician'],
        ];

        foreach ($guesses as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($topicLower, $needle) || str_contains(Str::lower($community->name), $needle)) {
                    return $key;
                }
            }
        }

        return $keys[0] ?? 'everyday';
    }

    private function titlePlaceholder(
        string $type,
        string $categoryKey,
        string $categoryLabel,
        string $topic,
        string $place,
    ): string {
        $topicPhrase = $this->readableTopic($topic, $categoryKey, $categoryLabel);
        $around = $place === 'your area' ? $place : $place;

        if ($type === Post::TYPE_OFFER) {
            return match ($categoryKey) {
                'jobs' => "e.g. I can help with {$topicPhrase} around {$around}",
                'housing' => "e.g. I can help with housing tips in {$around}",
                'healthcare' => "e.g. I can help with {$topicPhrase} in {$around}",
                'education' => "e.g. I can help with study or school questions in {$around}",
                'mentoring' => 'e.g. I can mentor someone starting out in my field',
                'training' => 'e.g. I can help with skills training or practice interviews',
                'career' => 'e.g. I can help with CV reviews and career planning',
                'language' => 'e.g. I can help with English conversation practice',
                'food_culture' => 'e.g. I can share recipes or help find African groceries',
                'business' => 'e.g. I can advise on starting a small side project',
                'travel_shipping' => 'e.g. I can help with shipping or sending things home',
                default => "e.g. I can help with {$topicPhrase} around {$around}",
            };
        }

        return match ($categoryKey) {
            'jobs' => "e.g. Looking for a {$topicPhrase} around {$around}",
            'housing' => "e.g. Looking for a flat or room in {$around}",
            'immigration' => "e.g. Need help with a visa question in {$around}",
            'healthcare' => "e.g. Looking for {$topicPhrase} around {$around}",
            'education' => "e.g. Looking for school or course advice in {$around}",
            'mentoring' => 'e.g. Looking for a mentor in my field',
            'training' => 'e.g. Looking for training or skills practice',
            'career' => 'e.g. Need career guidance or a CV review',
            'language' => 'e.g. Looking for English conversation practice',
            'food_culture' => 'e.g. Looking for a recipe or where to buy ingredients',
            'business' => 'e.g. Need advice on a small business or side project',
            'travel_shipping' => 'e.g. Need help shipping or sending something home',
            'childcare' => "e.g. Looking for childcare tips in {$around}",
            'transport' => "e.g. Need a lift or travel tip around {$around}",
            'legal' => "e.g. Need help with paperwork in {$around}",
            'money' => "e.g. Need banking or money advice in {$around}",
            default => "e.g. Looking for {$topicPhrase} around {$around}",
        };
    }

    private function bodyPlaceholder(string $type, string $categoryKey, string $place): string
    {
        if ($type === Post::TYPE_OFFER) {
            return "What you can do, how people should start, and any limits — especially around {$place}.";
        }

        return match ($categoryKey) {
            'jobs' => "Role, experience level, and what a helpful reply would include — keep it around {$place}.",
            'housing' => 'Budget, move-in timing, and area preferences. Never share your exact address here.',
            'immigration' => 'What stage you are at and what you need clarified. This is not legal advice.',
            default => "What you need, when you need it, and what a good reply looks like around {$place}.",
        };
    }

    private function readableTopic(string $topic, string $categoryKey, string $categoryLabel): string
    {
        $topic = trim($topic);

        if ($topic === '' || $topic === 'local help') {
            return match ($categoryKey) {
                'jobs' => 'job',
                'housing' => 'housing help',
                'healthcare' => 'healthcare help',
                default => Str::lower($categoryLabel),
            };
        }

        // "dentists" → "dentist job" when jobs category
        if ($categoryKey === 'jobs') {
            $base = Str::singular($topic);
            if (! str_contains($base, 'job')) {
                return $base.' job';
            }

            return $base;
        }

        if ($categoryKey === 'healthcare') {
            $base = Str::singular($topic);
            if (str_contains($base, 'dentist')) {
                return 'a dentist';
            }

            return $base.' help';
        }

        return $topic;
    }
}
