<?php

use App\Http\Controllers\RedirectMailSubdomain;
use Illuminate\Support\Facades\Route;

// Route::statamic('example', 'example-view', [
//    'title' => 'Example'
// ]);

// Redirect the mail subdomain to webmail. Registered here so it is matched
// before Statamic's catch-all, which is always the last route in the table.
// A 302 rather than a 301: browsers cache 301s indefinitely, and the target
// should stay changeable without visitors being stuck on the old one.
Route::domain(config('redirects.mail_subdomain.host'))->group(function () {
    Route::get('/{path?}', RedirectMailSubdomain::class)
        ->where('path', '.*')
        ->name('mail-subdomain.redirect');
});
