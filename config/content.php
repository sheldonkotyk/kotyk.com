<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content
    |--------------------------------------------------------------------------
    |
    | Pages and posts are Blade files under this directory, each opening with
    | YAML front matter in a Blade comment. See App\Content\ContentRepository.
    |
    */

    'path' => resource_path('content'),

    // Where each Livewire form's submissions are emailed.
    'forms' => [
        'contact' => [
            'to' => env('CONTACT_FORM_TO', 'sheldon@kotyk.com'),
            'subject' => 'Kotyk.com Contact Form Submission',
        ],
        'ds_dispatch_notifications' => [
            'to' => env('CONTACT_FORM_TO', 'sheldon@kotyk.com'),
            'subject' => 'DS Dispatch Notification Request',
        ],
    ],

];
