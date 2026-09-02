<?php

use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

return [

    /*
    |--------------------------------------------------------------------------
    | Active Static Caching Strategy
    |--------------------------------------------------------------------------
    |
    | To enable Static Caching, you should choose a strategy from the ones
    | you have defined below. Leave this null to disable static caching.
    |
    */

    'strategy' => env('STATAMIC_STATIC_CACHING_STRATEGY', null),

    /*
    |--------------------------------------------------------------------------
    | Caching Strategies
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the static caching strategies for your
    | application as well as their drivers.
    |
    | Supported drivers: "application", "file"
    |
    */

    'strategies' => [

        'half' => [
            'driver' => 'application',
            'expiry' => null,
        ],

        'full' => [
            'driver' => 'file',
            'path' => public_path('static'),
            'lock_hold_length' => 0,
            'permissions' => [
                'directory' => 0755,
                'file' => 0644,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Exclusions
    |--------------------------------------------------------------------------
    |
    | Here you may define a list of URLs to be excluded from static
    | caching. You may want to exclude URLs containing dynamic
    | elements like contact forms, or shopping carts.
    |
    */

    'exclude' => [

        'class' => null,

        'urls' => [
            // Renders {{ success }} / {{ errors }} after a POST, which a cached
            // page would never show. The spaces (Livewire) block on blog pages
            // is kept dynamic with {{ nocache }} instead, so /blog stays cached.
            '/contact',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Invalidation Rules
    |--------------------------------------------------------------------------
    |
    | Here you may define the rules that trigger when and how content would be
    | flushed from the static cache. See the documentation for more details.
    | If a custom class is not defined, the default invalidator is used.
    |
    | https://statamic.dev/static-caching
    |
    */

    'invalidation' => [

        'class' => null,

        'rules' => [
            //
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Ignoring Query Strings
    |--------------------------------------------------------------------------
    |
    | Statamic will cache pages of the same URL but with different query
    | parameters separately. This is useful for pages with pagination.
    | If you'd like to ignore the query strings, you may do so.
    |
    */

    // True so ?utm_source=, ?fbclid= and crawler junk collapse onto one cached
    // entry instead of each spawning its own. Safe here: every {{ paginate }}
    // block is commented out, so the site renders no ?page= links.
    'ignore_query_strings' => true,

    /*
    |--------------------------------------------------------------------------
    | Replacers
    |--------------------------------------------------------------------------
    |
    | Here you may define replacers that dynamically replace content within
    | the response. Each replacer must implement the Replacer interface.
    |
    */

    'replacers' => [
        CsrfTokenReplacer::class,
        NoCacheReplacer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm Queue
    |--------------------------------------------------------------------------
    |
    | Here you may define the name of the queue that requests will be pushed
    | onto when warming the static cache using the static:warm command.
    |
    */

    'warm_queue' => null,

];
