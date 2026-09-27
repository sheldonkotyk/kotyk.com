<?php

namespace Tests\Feature;

use App\Support\Image;
use Illuminate\Support\Facades\Storage;
use League\Glide\Server;
use Tests\TestCase;

class ImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('source');
        Storage::fake('image-cache');

        config([
            'images.source_disk' => 'source',
            'images.cache_disk' => 'image-cache',
        ]);

        // The server is a singleton built from config; rebuild it from the fakes.
        $this->app->forgetInstance(Server::class);

        $image = imagecreatetruecolor(3000, 2000);
        ob_start();
        imagepng($image);
        Storage::disk('source')->put('pages/photo (1).png', ob_get_clean());
    }

    public function test_it_serves_a_resized_webp_that_the_edge_may_cache(): void
    {
        $response = $this->get(Image::url('pages/photo (1).png', 'large'));

        $response->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->assertStringContainsString('s-maxage=31536000', $response->headers->get('Cache-Control'));
        $this->assertEmpty($response->headers->getCookies(), 'an image response must not set a session cookie');

        [$width] = getimagesizefromstring($response->streamedContent());
        $this->assertSame(1600, $width);
        $this->assertNotEmpty(Storage::disk('image-cache')->allFiles());
    }

    public function test_the_social_preset_crops_to_a_card(): void
    {
        $response = $this->get(Image::url('pages/photo (1).png', 'social'));

        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame([1200, 630], array_slice(getimagesizefromstring($response->streamedContent()), 0, 2));
    }

    public function test_query_parameters_cannot_change_the_output(): void
    {
        $response = $this->get(Image::url('pages/photo (1).png', 'small').'?w=2900&fm=png');

        [$width] = getimagesizefromstring($response->streamedContent());
        $this->assertSame(640, $width);
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_unknown_presets_and_missing_images_are_not_found(): void
    {
        $this->get('/img/huge/pages/photo%20(1).png')->assertNotFound();
        $this->get(Image::url('pages/missing.png'))->assertNotFound();
    }
}
