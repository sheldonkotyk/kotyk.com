<?php

namespace App\Content;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads pages and posts from resources/content.
 *
 * pages/about/colophon.blade.php is served at /about/colophon (pages/home is
 * /), and blog/{slug}.blade.php at /blog/{slug}. Only the front matter is
 * parsed here; bodies are rendered as ordinary views in the "content" namespace.
 */
class ContentRepository
{
    /** @var Collection<int, Entry>|null */
    private ?Collection $entries = null;

    public function __construct(private readonly string $root) {}

    /**
     * @return Collection<int, Entry>
     */
    public function all(): Collection
    {
        return $this->entries ??= collect(File::allFiles($this->root))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(fn ($file) => $this->parse($file->getRelativePathname(), $file->getContents()))
            ->values();
    }

    /**
     * Published pages and posts, whether listed yet or not.
     *
     * @return Collection<int, Entry>
     */
    public function published(): Collection
    {
        return $this->all()->filter->published->values();
    }

    public function page(string $uri): ?Entry
    {
        $uri = '/'.trim($uri, '/');

        return $this->published()->first(fn (Entry $entry) => ! $entry->isPost() && $entry->uri === $uri);
    }

    public function post(string $slug): ?Entry
    {
        return $this->published()->first(fn (Entry $entry) => $entry->isPost() && $entry->slug() === $slug);
    }

    /**
     * Listed posts, newest first.
     *
     * @return Collection<int, Entry>
     */
    public function posts(): Collection
    {
        return $this->all()
            ->filter(fn (Entry $entry) => $entry->isPost() && $entry->isListed())
            ->sortByDesc(fn (Entry $entry) => $entry->date)
            ->values();
    }

    /**
     * Listed posts that are writing rather than Midjourney experiments.
     *
     * @return Collection<int, Entry>
     */
    public function writing(): Collection
    {
        return $this->posts()->reject->isExperiment()->values();
    }

    /**
     * Listed Midjourney experiments, newest first.
     *
     * @return Collection<int, Entry>
     */
    public function experiments(): Collection
    {
        return $this->posts()->filter->isExperiment()->values();
    }

    /**
     * @return Collection<int, Entry>
     */
    public function navigation(): Collection
    {
        return $this->published()
            ->filter(fn (Entry $entry) => $entry->nav !== null)
            ->sortBy('nav')
            ->values();
    }

    /**
     * @return Collection<string, string> Slug => title, for tags in use.
     */
    public function tags(): Collection
    {
        $titles = Yaml::parseFile($this->root.'/tags.yaml') ?? [];

        return $this->all()
            ->filter->isListed()
            ->flatMap->tags
            ->unique()
            ->mapWithKeys(fn ($slug) => [$slug => $titles[$slug] ?? Str::headline($slug)])
            ->sort();
    }

    /**
     * @return Collection<int, Entry>
     */
    public function tagged(string $tag): Collection
    {
        return $this->all()
            ->filter(fn (Entry $entry) => $entry->isListed() && in_array($tag, $entry->tags, true))
            ->sortByDesc(fn (Entry $entry) => $entry->date ?? $entry->updated)
            ->values();
    }

    private function parse(string $path, string $source): Entry
    {
        preg_match('/\A\{\{--(.*?)--\}\}\s*/s', $source, $matches);

        $meta = $matches ? (Yaml::parse($matches[1]) ?? []) : [];
        $body = substr($source, strlen($matches[0] ?? ''));
        $name = Str::beforeLast($path, '.blade.php');
        $isPost = str_starts_with($name, 'blog/');
        $pageUri = Str::after($name, 'pages/');

        return new Entry(
            type: $isPost ? 'post' : 'page',
            uri: '/'.($isPost ? $name : ($pageUri === 'home' ? '' : $pageUri)),
            view: 'content::'.str_replace('/', '.', $name),
            title: (string) ($meta['title'] ?? Str::headline(basename($name))),
            date: $this->date($meta['date'] ?? null),
            updated: $this->date($meta['updated'] ?? null),
            published: ($meta['published'] ?? true) !== false,
            template: $meta['template'] ?? null,
            nav: isset($meta['nav']) ? (int) $meta['nav'] : null,
            tags: array_values((array) ($meta['tags'] ?? [])),
            featureImage: $meta['feature_image'] ?? null,
            teaserImage: $meta['teaser_image'] ?? null,
            description: $meta['description'] ?? null,
            excerpt: $this->excerpt($body),
            interactive: str_contains($body, '<livewire:'),
            words: str_word_count(strip_tags(preg_replace('#<(x-|livewire:)[^>]*?/>#s', ' ', $body))),
        );
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        return match (true) {
            $value === null => null,
            is_int($value) => CarbonImmutable::createFromTimestamp($value, config('app.timezone')),
            default => CarbonImmutable::parse($value, config('app.timezone')),
        };
    }

    /**
     * Plain-text opening of the body, for meta descriptions and teasers when
     * the front matter has no description.
     */
    private function excerpt(string $body, int $length = 160): string
    {
        $text = preg_replace('#<(x-|livewire:)[^>]*?/>|<x-[\w.-]+[^>]*>.*?</x-[\w.-]+>#s', ' ', $body);
        $text = str_replace(['@{{', '@{!!', '@@'], ['{{', '{!!', '@'], strip_tags($text));
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5)));

        return Str::limit($text, $length, '…', preserveWords: true);
    }
}
