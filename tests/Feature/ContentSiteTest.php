<?php

namespace Tests\Feature;

use App\Content\ContentRepository;
use App\Content\Entry;
use App\Mail\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContentSiteTest extends TestCase
{
    public function test_every_listed_page_and_post_renders_with_its_seo_head(): void
    {
        $entries = app(ContentRepository::class)->published()->filter->isListed();

        $this->assertNotEmpty($entries);

        $entries->each(function (Entry $entry) {
            $response = $this->get($entry->uri);

            $response->assertOk();
            $response->assertSee('<link rel="canonical" href="'.$entry->url().'">', false);
            $response->assertSee('<meta name="description" content="', false);
            $response->assertSee('<meta property="og:title"', false);
            $response->assertSee('<script type="application/ld+json">', false);
            $response->assertSee('content="index, follow', false);

            $this->assertSame(1, substr_count($response->getContent(), '<h1'), "{$entry->uri} should have exactly one h1");
        });
    }

    public function test_posts_are_described_as_articles(): void
    {
        $post = app(ContentRepository::class)->posts()->first();

        $response = $this->get($post->uri);

        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="article:published_time" content="'.$post->date->toIso8601String().'">', false);
        $response->assertSee('"@type":"BlogPosting"', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_home_page_uses_the_site_name_as_its_title(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>'.e(config('seo.site_name')).'</title>', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_blog_index_lists_posts_newest_first(): void
    {
        $posts = app(ContentRepository::class)->posts();

        $this->get('/blog')
            ->assertOk()
            ->assertSeeInOrder($posts->take(3)->map(fn (Entry $post) => e($post->title))->all(), false);
    }

    public function test_tag_pages_render_but_are_not_indexed(): void
    {
        $tag = app(ContentRepository::class)->tags()->keys()->first();

        $this->get('/tags')->assertOk();
        $this->get("/tags/{$tag}")->assertOk()->assertSee('content="noindex, follow"', false);
        $this->get('/tags/not-a-tag')->assertNotFound();
    }

    #[DataProvider('missingPaths')]
    public function test_unknown_and_unpublished_paths_are_not_found(string $path): void
    {
        $this->get($path)
            ->assertNotFound()
            ->assertSee('content="noindex, follow"', false);
    }

    public static function missingPaths(): array
    {
        return [
            'unknown page' => ['/no-such-page'],
            'unknown post' => ['/blog/no-such-post'],
            'unpublished page' => ['/episodes'],
        ];
    }

    public function test_sitemap_lists_every_listed_entry(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $xml = simplexml_load_string($response->getContent());
        $locs = [];

        foreach ($xml->url as $url) {
            $locs[] = (string) $url->loc;
        }

        app(ContentRepository::class)->published()->filter->isListed()
            ->each(fn (Entry $entry) => $this->assertContains($entry->url(), $locs));

        $this->assertNotContains(url('/episodes'), $locs);
    }

    public function test_feed_is_valid_atom(): void
    {
        $response = $this->get('/feed');

        $response->assertOk()->assertHeader('Content-Type', 'application/atom+xml; charset=utf-8');

        $xml = simplexml_load_string($response->getContent());

        $this->assertSame(config('seo.site_name'), (string) $xml->title);
        $this->assertCount(min(20, app(ContentRepository::class)->posts()->count()), $xml->entry);
    }

    public function test_pages_without_forms_do_not_load_livewire_and_are_edge_cacheable(): void
    {
        $response = $this->get('/about');

        $response->assertDontSee('data-csrf', false);
        $this->assertStringContainsString('s-maxage=', $response->headers->get('Cache-Control'));
    }

    public function test_pages_with_forms_load_livewire_and_are_not_edge_cached(): void
    {
        $response = $this->get('/contact');

        $response->assertOk()->assertSee('wire:submit="submit"', false);
        $this->assertStringNotContainsString('s-maxage=', $response->headers->get('Cache-Control'));
    }

    public function test_contact_form_emails_the_submission(): void
    {
        Mail::fake();

        Livewire::test('forms.contact')
            ->set('first_name', 'Ada')
            ->set('last_name', 'Lovelace')
            ->set('email', 'ada@example.com')
            ->set('comment', 'Hello there')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        Mail::assertSent(FormSubmission::class, fn (FormSubmission $mail) => $mail->hasTo(config('content.forms.contact.to'))
            && $mail->hasReplyTo('ada@example.com')
            && $mail->fields['Comment'] === 'Hello there');
    }

    public function test_contact_form_validates_and_ignores_honeypot_submissions(): void
    {
        Mail::fake();

        Livewire::test('forms.contact')
            ->call('submit')
            ->assertHasErrors(['first_name', 'last_name', 'email', 'comment']);

        Livewire::test('forms.contact')
            ->set('first_name', 'Bot')
            ->set('last_name', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('comment', 'Spam')
            ->set('full_name', 'I am a bot')
            ->call('submit')
            ->assertSet('sent', true);

        Mail::assertNothingSent();
    }

    public function test_ds_dispatch_form_emails_the_submission(): void
    {
        Mail::fake();

        Livewire::test('forms.ds-dispatch-notifications')
            ->set('name', 'Grace')
            ->set('notification_method', 'sms')
            ->set('phone', '555-555-5555')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        Mail::assertSent(FormSubmission::class, fn (FormSubmission $mail) => $mail->fields['Notification Method'] === 'sms');
    }
}
