<?php

// Laravel Cloud exposes its bucket credentials as a single JSON blob rather than
// discrete AWS_* variables. The framework only decodes it when LARAVEL_CLOUD=1 is
// set at the OS level, so decode it here to get the same disk locally and on Cloud.
$cloudDisks = collect(json_decode((string) env('LARAVEL_CLOUD_DISK_CONFIG', '[]'), true) ?: []);

$cloudDisk = $cloudDisks->firstWhere('is_default', true)
    ?? $cloudDisks->first()
    ?? [];

// Cloud names each bucket's entry by its disk, so a second bucket is selected
// by name rather than by being the default one.
$cloudGlideDisk = $cloudDisks->firstWhere('disk', 'glide') ?? [];

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => $cloudDisk['access_key_id'] ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => $cloudDisk['access_key_secret'] ?? env('AWS_SECRET_ACCESS_KEY'),
            'region' => $cloudDisk['default_region'] ?? env('AWS_DEFAULT_REGION'),
            'bucket' => $cloudDisk['bucket'] ?? env('AWS_BUCKET'),
            'url' => $cloudDisk['url'] ?? env('AWS_URL'),
            'endpoint' => $cloudDisk['endpoint'] ?? env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => $cloudDisk['use_path_style_endpoint'] ?? env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            // No 'visibility' key: the backing store is Cloudflare R2, which rejects
            // the per-object ACLs that Statamic's "public" visibility would send.
            // Objects are served publicly through the bucket 'url' above instead.
        ],

        // Glide derivative cache. Must be a different bucket from the assets disk:
        // glide:clear wipes this disk wholesale, and pointing it at the source
        // bucket would delete the originals. Public-read, because Statamic emits
        // this disk's URL directly into <img src> and PHP never serves the file.
        'glide' => [
            'driver' => 's3',
            'key' => $cloudGlideDisk['access_key_id'] ?? env('AWS_ACCESS_KEY_ID'),
            'secret' => $cloudGlideDisk['access_key_secret'] ?? env('AWS_SECRET_ACCESS_KEY'),
            'region' => $cloudGlideDisk['default_region'] ?? env('AWS_DEFAULT_REGION'),
            'bucket' => $cloudGlideDisk['bucket'] ?? env('AWS_BUCKET_GLIDE'),
            'url' => $cloudGlideDisk['url'] ?? env('AWS_URL_GLIDE'),
            'endpoint' => $cloudGlideDisk['endpoint'] ?? env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => $cloudGlideDisk['use_path_style_endpoint'] ?? false,
            'throw' => false,
        ],

        'assets' => [
            'driver' => 'local',
            'root' => public_path('assets'),
            'url' => '/assets',
            'visibility' => 'public',
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
