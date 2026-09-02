<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Edge Cache
    |--------------------------------------------------------------------------
    |
    | Laravel sends "Cache-Control: no-cache, private" and a session cookie on
    | every response, and Cloudflare will not cache a response carrying
    | Set-Cookie. The result is that nothing reaches the edge cache: every hit,
    | including crawlers, is served by the origin. On a scale-to-zero
    | environment that also means every hit after the sleep timeout wakes the
    | container back up.
    |
    | App\Http\Middleware\SetEdgeCacheHeaders makes the pages that carry no
    | per-visitor state cacheable instead, and strips the session cookie from
    | those responses so the edge is willing to store them.
    |
    */

    'ttl' => env('EDGE_CACHE_TTL', 3600),

    /*
    | Paths never made cacheable, on top of the static caching exclusions in
    | config/statamic/static_caching.php - that list already names every URL
    | carrying a form, and there is no reason to maintain a second copy of it.
    */

    'exclude' => [
        '/!/*',   // Statamic action routes: form posts, live preview, glide
        '/cp',    // control panel, disabled in production but never cacheable
        '/cp/*',
        '/up',    // health check
    ],

];
