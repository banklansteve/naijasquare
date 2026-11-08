<?php

namespace App\Support;

use Carbon\CarbonInterface;

class HumanTime
{
    public static function format(?CarbonInterface $time): ?string
    {
        if ($time === null) {
            return null;
        }

        if ($time->isYesterday()) {
            return 'Yesterday';
        }

        return $time->diffForHumans();
    }
}
