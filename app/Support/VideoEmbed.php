<?php

namespace App\Support;

/**
 * Turns a YouTube or Vimeo page URL into its embeddable player URL.
 */
class VideoEmbed
{
    public static function url(string $url): string
    {
        $parts = parse_url($url);
        $host = preg_replace('/^(www\.|m\.)/', '', $parts['host'] ?? '');
        parse_str($parts['query'] ?? '', $query);
        $path = trim($parts['path'] ?? '', '/');

        $youtubeId = match ($host) {
            'youtu.be' => $path,
            'youtube.com' => $query['v'] ?? (preg_match('#^(?:embed|shorts|live)/([\w-]+)#', $path, $m) ? $m[1] : null),
            default => null,
        };

        if ($youtubeId) {
            $start = isset($query['t']) ? (int) $query['t'] : null;

            return 'https://www.youtube-nocookie.com/embed/'.$youtubeId.($start ? '?start='.$start : '');
        }

        if ($host === 'vimeo.com' && preg_match('#^(\d+)#', $path, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}
