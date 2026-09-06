<?php

namespace App\Http\Middleware;

use Abigah\BotCopTrafficClient\Http\Middleware\NeverCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps /up out of every cache.
 *
 * A cached 200 on the health route is a check that never reached the origin:
 * the prober sees a healthy site for as long as the cache holds, which is
 * exactly the window it was meant to be raising the alarm in. A confident
 * wrong answer, rather than no answer.
 *
 * Registered globally and scoped by path here, rather than attached to the
 * route, because Laravel's built-in health route — `withRouting(health: '/up')`
 * — is registered with no middleware group at all. There is nothing to append
 * to. Defining our own /up instead would mean re-implementing the
 * DiagnosingHealth dispatch and the maintenance-mode exemption, and registering
 * it ahead of Statamic's catch-all, which is a lot of moving parts for a
 * header.
 *
 * Global also means this unwinds *after* the web group, so it wins over
 * SetEdgeCacheHeaders rather than racing it. That does not currently matter —
 * the health route is outside the web group, so SetEdgeCacheHeaders never sees
 * it — but it is what stops this quietly breaking if that ever changes.
 */
class NeverCacheHealth
{
    public function __construct(private readonly NeverCache $neverCache) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Matches the path given to withRouting(health: ...) in bootstrap/app.php.
        if (! $request->is('up')) {
            return $next($request);
        }

        return $this->neverCache->handle($request, $next);
    }
}
