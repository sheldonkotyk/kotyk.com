<?php

namespace App\Content;

use Carbon\CarbonImmutable;

/**
 * A page or blog post: one Blade file under resources/content, described by
 * the YAML front matter in its leading Blade comment.
 */
final readonly class Entry
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $type,
        public string $uri,
        public string $view,
        public string $title,
        public ?CarbonImmutable $date,
        public ?CarbonImmutable $updated,
        public bool $published,
        public ?string $template,
        public ?int $nav,
        public array $tags,
        public ?string $featureImage,
        public ?string $teaserImage,
        public ?string $description,
        public string $excerpt,
        public bool $interactive,
    ) {}

    public function isPost(): bool
    {
        return $this->type === 'post';
    }

    public function slug(): string
    {
        return basename($this->uri);
    }

    public function url(): string
    {
        return url($this->uri);
    }

    /**
     * Future-dated posts are reachable by URL but kept out of listings, the
     * sitemap and the feed until their date arrives.
     */
    public function isListed(): bool
    {
        return $this->published && ($this->date === null || $this->date->isPast());
    }

    public function summary(): string
    {
        return $this->description ?? $this->excerpt;
    }

    public function lastModified(): ?CarbonImmutable
    {
        return collect([$this->updated, $this->date])->filter()->max();
    }

    public function image(): ?string
    {
        return $this->featureImage ?? $this->teaserImage;
    }
}
