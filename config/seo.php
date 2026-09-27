<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    |
    | Used for <title>, og:site_name, the feed, and the WebSite and Person
    | nodes in every page's JSON-LD. The description is the fallback for any
    | page that has neither a description nor body text of its own.
    |
    */

    'site_name' => 'Kotyk.com - The online abode of Sheldon Kotyk',

    'description' => 'Sheldon Kotyk writes about leadership, faith, technology and family. Husband, dad, and digital strategist with Power to Change.',

    'language' => 'en-CA',

    'locale' => 'en_CA',

    // Shared-card image for pages without a feature image of their own.
    'image' => '/favicons/android-chrome-512x512.png',

    'author' => [
        'name' => 'Sheldon Kotyk',
        'job_title' => 'Digital Strategist',
        'image' => '/favicons/android-chrome-512x512.png',
    ],

    'twitter' => '@shoden',

    // Profiles that are the same person, for JSON-LD sameAs and rel="me".
    'social' => [
        'x' => 'https://x.com/shoden',
        'instagram' => 'https://instagram.com/shoden',
        'github' => 'https://github.com/sheldonkotyk',
        'linkedin' => 'https://www.linkedin.com/in/sheldonkotyk/',
    ],

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', 'R1q2uTTXPBbj_pgmOXL5-Ml3F8BeIYTFZ1S3C4cn3RE'),

];
