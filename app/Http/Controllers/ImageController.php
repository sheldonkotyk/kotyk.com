<?php

namespace App\Http\Controllers;

use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function __invoke(Server $glide, string $preset, string $path): StreamedResponse
    {
        abort_unless(array_key_exists($preset, config('images.presets')), 404);

        try {
            // Only the preset reaches Glide; query string parameters are ignored.
            $cachePath = $glide->makeImage($path, ['p' => $preset]);
        } catch (FileNotFoundException) {
            abort(404);
        }

        $cache = $glide->getCache();

        return response()->stream(function () use ($cache, $cachePath) {
            fpassthru($cache->readStream($cachePath));
        }, 200, [
            'Content-Type' => $cache->mimeType($cachePath),
            'Content-Length' => $cache->fileSize($cachePath),
            // A preset's output for a given source never changes, so the edge
            // may keep it for a year and browsers for a week.
            'Cache-Control' => 'public, max-age=604800, s-maxage=31536000',
        ]);
    }
}
