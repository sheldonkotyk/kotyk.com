<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make pages that carry no per-visitor state cacheable at the edge.
 *
 * Laravel answers every request with "Cache-Control: no-cache, private" and a
 * session cookie, and Cloudflare will not cache a response carrying Set-Cookie.
 * So nothing is cached: cf-cache-status is DYNAMIC, and even /build/assets/*
 * shows up in the origin access log. On a scale-to-zero environment that means
 * a single crawler is enough to wake the container.
 *
 * Must be PREPENDED to the web group. Middleware unwinds in reverse on the way
 * out, so being first on the way in makes this last on the way out - after
 * StartSession and AddQueuedCookiesToResponse have attached their cookies,
 * which is the only point at which they can be removed.
 */
class SetEdgeCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isCacheable($request, $response)) {
            return $response;
        }

        // Without this the response still carries Set-Cookie and the edge
        // declines to store it, however permissive Cache-Control is.
        foreach ([config('session.cookie'), 'XSRF-TOKEN'] as $cookie) {
            $response->headers->removeCookie($cookie, config('session.path'), config('session.domain'));
        }

        // max-age=0 keeps browsers revalidating, so a purge is visible to
        // someone who has already loaded the page; s-maxage is what the edge
        // actually holds it for. Purge-on-deploy handles releases.
        $response->headers->set(
            'Cache-Control',
            'public, max-age=0, s-maxage='.(int) config('edge_cache.ttl', 3600)
        );

        return $response;
    }

    private function isCacheable(Request $request, Response $response): bool
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        $path = '/'.ltrim($request->path(), '/');

        $excluded = array_merge(
            config('edge_cache.exclude', []),
            // Same list the static cache excludes: every URL carrying a form.
            config('statamic.static_caching.exclude.urls', []),
        );

        foreach ($excluded as $pattern) {
            if ($request->is(ltrim($pattern, '/')) || $path === rtrim($pattern, '*')) {
                return false;
            }
        }

        // Backstop for the failure this design is most exposed to: a form added
        // to a page nobody remembered to exclude. Caching a CSRF token at the
        // edge would hand every visitor the same one.
        if ($this->containsCsrfToken($response)) {
            return false;
        }

        return true;
    }

    private function containsCsrfToken(Response $response): bool
    {
        $content = $response->getContent();

        if (! is_string($content)) {
            return false;
        }

        return str_contains($content, 'name="_token"')
            || str_contains($content, 'name="csrf-token"');
    }
}
