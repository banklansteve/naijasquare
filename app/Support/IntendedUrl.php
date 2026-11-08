<?php

namespace App\Support;

use Illuminate\Http\Request;

class IntendedUrl
{
    public static function captureFromRequest(Request $request): void
    {
        $candidate = $request->query('redirect');

        if (! is_string($candidate) || $candidate === '') {
            return;
        }

        self::store($request, $candidate);
    }

    public static function store(Request $request, string $url): void
    {
        $safe = self::sanitize($request, $url);

        if ($safe !== null) {
            $request->session()->put('url.intended', $safe);
        }
    }

    public static function rememberCurrentIfMissing(Request $request): void
    {
        if ($request->session()->has('url.intended')) {
            return;
        }

        if (! $request->isMethod('GET')) {
            return;
        }

        self::store($request, $request->fullUrl());
    }

    public static function sanitize(Request $request, string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            if (str_starts_with($url, '//')) {
                return null;
            }

            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'])) {
            return null;
        }

        if (strcasecmp($parts['host'], $request->getHost()) !== 0) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return $path.$query;
    }
}
