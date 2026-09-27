<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    |
    | Content images are resized by Glide and served from /img/{preset}/{path}.
    | Only the presets below exist: a URL cannot ask for an arbitrary size, so
    | nobody can make the server render thousands of variants of one image.
    |
    | Originals come from the content assets disk. Each derivative is made once
    | and kept on the cache disk. In production that is the glide bucket; any
    | other environment keeps them locally, so development renders never write
    | derivatives into the production bucket.
    |
    */

    // Where the image paths in front matter and content components resolve.
    'source_disk' => env('CONTENT_ASSETS_DISK', 's3'),

    'cache_disk' => env('IMAGES_CACHE_DISK', env('APP_ENV') === 'production' ? 'glide' : 'local'),

    'cache_prefix' => 'glide',

    'driver' => env('IMAGES_DRIVER', 'gd'),

    // Refuse to process anything larger than this many pixels.
    'max_image_size' => 5000 * 5000,

    'presets' => [
        // Floated beside text (max-w-xs), at 2x.
        'small' => ['w' => 640, 'fm' => 'webp', 'q' => 80],
        // Post cards in listings.
        'card' => ['w' => 800, 'h' => 500, 'fit' => 'crop', 'fm' => 'webp', 'q' => 80],
        // Full-width content and feature images.
        'large' => ['w' => 1600, 'fm' => 'webp', 'q' => 80],
        // Open Graph / Twitter card: the 1.91:1 size every platform accepts.
        // JPEG, because not every link-preview crawler reads WebP.
        'social' => ['w' => 1200, 'h' => 630, 'fit' => 'crop', 'fm' => 'jpg', 'q' => 85],
    ],

];
