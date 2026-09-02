<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Send any hostname that is not the canonical one to the canonical one.
 *
 * The environment serves a wildcard domain, so *every* subdomain reaches this
 * app and renders the full site. SEO Pro builds its canonical URL from the
 * request host, so each of those hostnames declares itself canonical - which,
 * with X-Robots-Tag set to "index, follow", makes them indexable duplicates
 * that anyone can create just by linking to one.
 */
class RedirectNonCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        // Laravel Cloud health checks and internal routing do not arrive on the
        // canonical hostname. Redirecting those would fail the health check and
        // take the environment down, so they are left alone.
        if ($request->is('up') || str_ends_with($request->getHost(), '.laravel.cloud')) {
            return $next($request);
        }

        $canonical = parse_url(config('app.url'), PHP_URL_HOST);

        $allowed = array_filter([
            $canonical,
            // Has its own route; redirecting it here would pre-empt that.
            config('redirects.mail_subdomain.host'),
        ]);

        if (in_array($request->getHost(), $allowed, true)) {
            return $next($request);
        }

        // 301: this is a canonical-host rule, so search engines should
        // consolidate onto the canonical hostname rather than keep both.
        return redirect()->to(
            rtrim(config('app.url'), '/').'/'.ltrim($request->getRequestUri(), '/'),
            301
        );
    }
}
