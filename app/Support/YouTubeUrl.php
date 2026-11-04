<?php

namespace App\Support;

class YouTubeUrl
{
    private const ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    private const HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'music.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
    ];

    private const PATH_PREFIXES = ['shorts', 'embed', 'live', 'v'];

    /**
     * Extract the 11-character video ID from a YouTube URL, or null if the
     * value is not a recognised YouTube video URL.
     */
    public static function extractId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $segments = array_values(array_filter(explode('/', $parts['path'] ?? ''), 'strlen'));

        if ($host === 'youtu.be' || $host === 'www.youtu.be') {
            return self::valid($segments[0] ?? null);
        }

        if (! in_array($host, self::HOSTS, true)) {
            return null;
        }

        if (($segments[0] ?? null) === 'watch') {
            parse_str($parts['query'] ?? '', $query);

            return self::valid(is_string($query['v'] ?? null) ? $query['v'] : null);
        }

        if (in_array($segments[0] ?? null, self::PATH_PREFIXES, true)) {
            return self::valid($segments[1] ?? null);
        }

        return null;
    }

    private static function valid(?string $id): ?string
    {
        return $id !== null && preg_match(self::ID_PATTERN, $id) ? $id : null;
    }
}
