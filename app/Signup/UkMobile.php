<?php

namespace App\Signup;

class UkMobile
{
    /**
     * Canonical +447… form, or null when the value is not a UK mobile.
     */
    public static function normalise(string $input): ?string
    {
        $compact = preg_replace('/[^\d+]/', '', trim($input)) ?? '';

        if (str_starts_with($compact, '0044')) {
            $compact = '0'.substr($compact, 4);
        } elseif (str_starts_with($compact, '+44')) {
            $compact = '0'.substr($compact, 3);
        } elseif (str_starts_with($compact, '44') && strlen($compact) === 12) {
            $compact = '0'.substr($compact, 2);
        }

        if (! preg_match('/^07([1-57-9]\d{8})$/', $compact, $matches)) {
            return null;
        }

        return '+447'.$matches[1];
    }
}
