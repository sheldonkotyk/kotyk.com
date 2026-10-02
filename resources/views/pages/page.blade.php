<?php

use App\Content\ContentRepository;
use App\Content\Entry;
use App\View\Head;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $uri = '';

    public function mount(string $uri = ''): void
    {
        // Livewire marks every response it renders no-store. Content pages are
        // static HTML; a nested form component switches this back off on boot.
        $this->enableBackButtonCache();

        $this->uri = $uri;

        abort_unless($this->entry, 404);

        $head = app(Head::class)->forEntry($this->entry);

        // The home page is the site itself: its title is the site name alone.
        if ($this->isHome()) {
            $head->title = null;
        }

        // Each ancestor of a nested page (/about for /about/colophon) that
        // exists as a page of its own becomes a breadcrumb.
        $content = app(ContentRepository::class);
        $path = '';

        foreach (explode('/', trim($this->uri, '/')) as $segment) {
            $path .= '/'.$segment;
            $ancestor = $content->page($path);

            if ($ancestor && $this->uri !== '') {
                $head->breadcrumbs[$ancestor->title] = $ancestor->url();
            }
        }

        if (count($head->breadcrumbs) < 2) {
            $head->breadcrumbs = [];
        }
    }

    #[Computed]
    public function entry(): ?Entry
    {
        return app(ContentRepository::class)->page($this->uri);
    }

    public function isHome(): bool
    {
        return $this->entry->uri === '/';
    }
};
?>

<div>
    @if ($this->isHome())
        <h1 class="sr-only">{{ config('seo.author.name') }}</h1>
        <x-content.body class="pt-10 md:pt-14">
            @include($this->entry->view)
        </x-content.body>
    @else
        <article class="pt-10 md:pt-14">
            <header class="mx-auto max-w-6xl px-4 md:px-8">
                <h1 class="max-w-3xl font-semiwide text-4xl md:text-6xl font-bold tracking-tight text-balance">{{ $this->entry->title }}</h1>
                @if ($this->entry->featureImage)
                    <img class="mt-8 w-full max-w-4xl rounded-sm" src="{{ \App\Support\Image::url($this->entry->featureImage) }}" alt="" fetchpriority="high">
                @endif
            </header>
            <x-content.body class="mt-8">
                @include($this->entry->view)
            </x-content.body>
        </article>
    @endif
</div>
