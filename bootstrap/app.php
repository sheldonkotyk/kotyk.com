<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The environment serves *.kotyk.com, so every subdomain reaches the app
        // and renders an indexable copy of the site. Send them to the canonical host.
        $middleware->web(append: [
            \App\Http\Middleware\RedirectNonCanonicalHost::class,
        ]);

        // Prepended, not appended: middleware unwinds in reverse, so being
        // first on the way in makes this last on the way out - after
        // StartSession has attached the session cookie it needs to remove.
        $middleware->web(prepend: [
            \App\Http\Middleware\SetEdgeCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
