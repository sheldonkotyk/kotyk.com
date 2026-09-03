<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Send every request on the mail subdomain to webmail.
 *
 * A controller rather than a closure because `route:cache` serialises the route
 * table and cannot serialise a closure, which would fail the whole command.
 */
class RedirectMailSubdomain extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->away(config('redirects.mail_subdomain.target'));
    }
}
