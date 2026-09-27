<?php

namespace App\Support;

/**
 * URLs for content images, resized to one of the presets in config/images.php.
 */
class Image
{
    public static function url(?string $path, string $preset = 'large'): ?string
    {
        if (! $path) {
            return null;
        }

        $path = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return url("/img/{$preset}/{$path}");
    }
}
