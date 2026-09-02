<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mail Subdomain
    |--------------------------------------------------------------------------
    |
    | mail.kotyk.com is a bookmark shortcut to webmail. It used to be a DNS
    | record pointing straight at ghs.google.com, which serves no usable
    | certificate for this hostname - fine over plain HTTP, but broken the
    | moment HSTS includeSubDomains forces HTTPS. Serving the redirect from
    | the app instead means the hostname gets a real certificate like any
    | other domain on the environment.
    |
    | Read through config so it survives config:cache; env() outside a config
    | file returns null once the config is cached.
    |
    */

    'mail_subdomain' => [
        'host' => env('MAIL_SUBDOMAIN_HOST', 'mail.kotyk.com'),
        'target' => env('MAIL_SUBDOMAIN_TARGET', 'https://mail.google.com/a/kotyk.com'),
    ],

];
