<?php

namespace App\Community;

use App\Models\Community;
use Illuminate\Support\Str;

class SuggestNeedDetails
{
    /**
     * Template-based draft details for a Need. Not an LLM — practical starter text the user can edit.
     */
    public function for(
        string $title,
        string $categoryKey,
        ?string $locationLabel = null,
        ?Community $community = null,
    ): string {
        $title = trim(preg_replace('/\s+/', ' ', $title) ?? '');
        $place = $this->place($locationLabel, $community);
        $topic = $this->topicFromTitle($title, $categoryKey);

        $opening = match ($categoryKey) {
            'housing' => "I'm looking for housing help{$place}.",
            'jobs' => "I'm looking for help with work or applications{$place}.",
            'immigration' => "I need practical guidance on an immigration or paperwork question{$place}.",
            'money' => "I need help with a money or banking question{$place}.",
            'education' => "I need advice about school, courses, or studying{$place}.",
            'healthcare' => "I'm looking for practical health or wellbeing help{$place}.",
            'mentoring' => "I'm looking for mentoring or guidance{$place}.",
            'childcare' => "I need help with childcare or family logistics{$place}.",
            'legal' => "I need help understanding a paperwork or legal next step{$place}.",
            'travel_shipping' => "I need help with travel, shipping, or sending something home{$place}.",
            'business' => "I need advice on a small business or side project{$place}.",
            default => "I could use practical help with this{$place}.",
        };

        $context = match ($categoryKey) {
            'housing' => 'Budget, timing, and area preferences would help — I can share those once someone replies. Please keep advice local and scam-aware.',
            'jobs' => 'A useful reply would include what to try next, who to ask, or a realistic tip from experience.',
            'immigration' => 'I understand this is not legal advice — I am looking for lived experience and clear next steps.',
            'money' => 'I will not share account numbers or passwords here. Practical steps or what to watch out for would help.',
            'healthcare' => 'I am looking for practical pointers (how to register, what to ask, what helped you), not a diagnosis.',
            default => 'A useful reply would tell me what to try next, what to prepare, or who tends to know this locally.',
        };

        $closing = 'Thanks in advance — even a short tip helps.';

        return trim(implode("\n\n", array_filter([$opening, $topic, $context, $closing])));
    }

    private function place(?string $locationLabel, ?Community $community): string
    {
        $label = is_string($locationLabel) && trim($locationLabel) !== ''
            ? trim($locationLabel)
            : (is_string($community?->city) && $community->city !== '' ? $community->city : null);

        if ($label === null && is_string($community?->location_label) && $community->location_label !== '') {
            $label = $community->location_label;
        }

        if ($label === null) {
            return '';
        }

        return ' around '.$label;
    }

    private function topicFromTitle(string $title, string $categoryKey): string
    {
        if ($title === '') {
            return 'I can share more detail about what I need.';
        }

        $clean = Str::of($title)
            ->replaceMatches('/^(looking for|need|needs|help with|seeking)\s+/iu', '')
            ->trim()
            ->toString();

        if ($clean === '') {
            $clean = $title;
        }

        $label = (string) config("posts.categories.{$categoryKey}.label", 'this');

        return "In short: {$clean}. Happy to clarify anything about {$label}.";
    }
}
