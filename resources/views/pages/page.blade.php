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
        @php($season = ['Winter', 'Winter', 'Spring', 'Spring', 'Spring', 'Summer', 'Summer', 'Summer', 'Fall', 'Fall', 'Fall', 'Winter'][now()->month - 1])
        {{-- The home page is a prairie seed catalogue: the masthead, the
             grower's word beside today's growing conditions, then the latest
             posts as seed packets and an order form. --}}
        <header class="mx-auto max-w-6xl px-4 pt-12 md:px-8 md:pt-16 text-center">
            <div class="border-y-4 border-double border-seed-green py-4 dark:border-[#8fc79a]">
                <p class="font-serif italic text-seed-red dark:text-[#e98a80]">{{ $season }} {{ now()->year }}</p>
                <p class="mt-1 font-tall font-black uppercase leading-[0.85] text-5xl sm:text-7xl md:text-8xl text-seed-green dark:text-[#8fc79a]">The Kotyk Seed Catalogue</p>
                <p class="mt-2 font-serif italic text-seed-red dark:text-[#e98a80]">Stories grown in Steinbach, Manitoba</p>
            </div>
        </header>

        <div class="mx-auto mt-12 grid max-w-6xl gap-10 px-4 md:px-8 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-16">
            <div>
                <h2 class="font-serif italic text-xl text-seed-red dark:text-[#e98a80]">From the grower</h2>
                <div class="mt-3 prose prose-lg md:prose-xl dark:prose-invert max-w-2xl md:prose-p:first-of-type:text-[1.4em] md:prose-p:first-of-type:leading-snug">
                    @include($this->entry->view)
                </div>
            </div>
            <x-home.almanac class="self-start lg:mt-10" />
        </div>

        @php($content = app(\App\Content\ContentRepository::class))
        {{-- The packets are for real writing: no Midjourney experiments, and
             nothing too short to read as a post. --}}
        <x-home.catalogue
            :posts="$content->writing()->filter(fn ($post) => $post->words >= 150)->take(4)"
            :experiments="$content->experiments()->take(5)"
            class="mt-20" />
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
