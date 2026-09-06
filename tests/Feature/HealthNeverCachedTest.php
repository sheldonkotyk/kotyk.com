<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * A cached 200 on /up is a check that never reached the origin, which is worse
 * than no monitoring because it is confidently wrong.
 */
class HealthNeverCachedTest extends TestCase
{
    public function test_the_health_route_may_not_be_stored_anywhere(): void
    {
        $response = $this->get('/up');

        $response->assertOk();

        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);

        // Cloudflare and most CDNs honour this even under a rule that ignores
        // Cache-Control, which a "Cache Everything" rule does.
        $this->assertSame('no-store', $response->headers->get('CDN-Cache-Control'));
    }

    public function test_it_does_not_touch_anything_else(): void
    {
        // The site's edge caching is what keeps a scale-to-zero container
        // asleep; a global middleware that broke it would be an expensive fix.
        $response = $this->get('/');

        $response->assertOk();
        $this->assertNull($response->headers->get('CDN-Cache-Control'));
        $this->assertStringNotContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
