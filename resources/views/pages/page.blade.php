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
        @php($content = app(\App\Content\ContentRepository::class))
        {{-- The home page is a handful of postcards: one from Steinbach with
             the intro on the back, the latest writing as photo cards that
             turn over, the day's weather, and the Midjourney experiments as
             novelty cards. --}}
        <div class="mx-auto max-w-6xl px-4 pt-12 md:px-8 md:pt-16">
            <x-home.greeting>
                @include($this->entry->view)
            </x-home.greeting>

            <section class="mt-20" aria-labelledby="latest-writing">
                <h2 id="latest-writing" class="font-semiwide text-3xl md:text-4xl font-bold tracking-tight">The latest writing</h2>
                <p class="mt-2 font-serif italic text-zinc-600 dark:text-zinc-400">The first few lines of each are on the back.</p>
                {{-- Real writing only: no Midjourney experiments, and nothing
                     too short to read as a post. --}}
                <x-home.postcards :posts="$content->writing()->filter(fn ($post) => $post->words >= 150)->take(4)" class="mt-8" />
            </section>

            <div class="mt-20 grid gap-12 lg:grid-cols-[22rem_minmax(0,1fr)] lg:gap-16">
                <x-home.almanac class="self-start" />
                <x-home.novelties :experiments="$content->experiments()->take(5)" />
            </div>

            <p class="mt-16 border-t border-frost pt-6 font-serif text-lg dark:border-zinc-800">
                More in the mailbag:
                <a href="/blog" class="link">every post</a> ({{ $content->writing()->count() }}),
                <a href="/tags/midjourney" class="link">the novelty cards</a> ({{ $content->experiments()->count() }}),
                and <a href="{{ route('feed') }}" class="link">the feed</a>, for new ones as they're sent.
            </p>
        </div>
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
