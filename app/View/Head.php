<?php

namespace App\View;

use App\Content\Entry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Everything the layout's <head> needs for the current request: title, meta
 * description, canonical URL, Open Graph / Twitter cards, JSON-LD, and whether
 * the page carries an interactive Livewire component.
 *
 * Bound per request. Page components fill it in while they mount, which
 * happens before the layout around them renders.
 */
class Head
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $image = null;

    public ?string $imageAlt = null;

    public string $type = 'website';

    public ?string $canonical = null;

    public ?CarbonImmutable $publishedAt = null;

    public ?CarbonImmutable $modifiedAt = null;

    /** @var list<string> */
    public array $tags = [];

    public bool $noindex = false;

    /** @var array<string, string> Label => URL, not including the home page. */
    public array $breadcrumbs = [];

    /** @var list<array<string, mixed>> Extra schema.org nodes for this page. */
    public array $schema = [];

    /**
     * Livewire's script carries a CSRF token, which makes a page uncacheable
     * at the edge, so it is only loaded where a component needs it.
     */
    public bool $livewire = false;

    public function forEntry(Entry $entry): static
    {
        $this->title = $entry->title;
        $this->description = $entry->summary() ?: null;
        $this->image = Entry::assetUrl($entry->image());
        $this->imageAlt = $entry->image() ? $entry->title : null;
        $this->canonical = $entry->url();
        $this->modifiedAt = $entry->lastModified();
        $this->livewire = $entry->interactive;

        if ($entry->isPost()) {
            $this->type = 'article';
            $this->publishedAt = $entry->date;
            $this->tags = $entry->tags;
        }

        return $this;
    }

    public function documentTitle(): string
    {
        $site = config('seo.site_name');

        return $this->title && $this->title !== $site ? "{$this->title} | {$site}" : $site;
    }

    public function metaDescription(): string
    {
        return Str::limit($this->description ?: config('seo.description'), 160, '…', preserveWords: true);
    }

    public function canonicalUrl(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function imageUrl(): ?string
    {
        return $this->image ?? (config('seo.image') ? url(config('seo.image')) : null);
    }

    /**
     * The page's JSON-LD graph: the site and its author on every page, plus a
     * breadcrumb trail and whatever the page itself describes.
     *
     * @return array<string, mixed>
     */
    public function jsonLd(): array
    {
        $home = url('/');
        $person = ['@id' => $home.'#person'];

        $webPage = array_filter([
            '@type' => $this->type === 'article' ? 'BlogPosting' : 'WebPage',
            '@id' => $this->canonicalUrl().'#webpage',
            'url' => $this->canonicalUrl(),
            'name' => $this->title ?? config('seo.site_name'),
            'headline' => $this->type === 'article' ? $this->title : null,
            'description' => $this->metaDescription(),
            'image' => $this->image,
            'inLanguage' => config('seo.language'),
            'isPartOf' => ['@id' => $home.'#website'],
            'author' => $this->type === 'article' ? $person : null,
            'publisher' => $this->type === 'article' ? $person : null,
            'datePublished' => $this->publishedAt?->toIso8601String(),
            'dateModified' => $this->modifiedAt?->toIso8601String(),
            'keywords' => $this->tags ? implode(', ', $this->tags) : null,
            'mainEntityOfPage' => $this->type === 'article' ? $this->canonicalUrl() : null,
            'breadcrumb' => $this->breadcrumbs ? ['@id' => $this->canonicalUrl().'#breadcrumb'] : null,
        ]);

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $home.'#website',
                'url' => $home,
                'name' => config('seo.site_name'),
                'description' => config('seo.description'),
                'inLanguage' => config('seo.language'),
                'publisher' => $person,
            ],
            [
                '@type' => 'Person',
                '@id' => $home.'#person',
                'name' => config('seo.author.name'),
                'url' => $home,
                'image' => url(config('seo.author.image')),
                'jobTitle' => config('seo.author.job_title'),
                'sameAs' => array_values(config('seo.social')),
            ],
            $webPage,
        ];

        if ($this->breadcrumbs) {
            $position = 1;

            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $this->canonicalUrl().'#breadcrumb',
                'itemListElement' => collect(['Home' => $home] + $this->breadcrumbs)
                    ->map(fn ($url, $name) => [
                        '@type' => 'ListItem',
                        'position' => $position++,
                        'name' => $name,
                        'item' => $url,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return ['@context' => 'https://schema.org', '@graph' => [...$graph, ...$this->schema]];
    }
}
