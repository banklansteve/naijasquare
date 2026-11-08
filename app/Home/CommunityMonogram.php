<?php

namespace App\Home;

class CommunityMonogram
{
    /**
     * Stable palette token from the community name.
     *
     * @return array{initials: string, tone: string}
     */
    public function for(string $name): array
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        if ($initials === '') {
            $initials = 'C';
        }

        $tones = ['lagoon', 'moss', 'clay', 'honey'];
        $hash = crc32(mb_strtolower($name));
        $tone = $tones[$hash % count($tones)];

        return [
            'initials' => $initials,
            'tone' => $tone,
        ];
    }
}
