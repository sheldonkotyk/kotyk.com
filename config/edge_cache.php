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
    | Paths never made cacheable. Pages carrying a form need no entry here:
    | the middleware finds their CSRF token (Livewire's data-csrf included)
    | and skips them.
    */

    'exclude' => [
        '/up',    // health check
    ],

];
