<?php

use App\Content\ContentRepository;
use App\Content\Entry;
use App\View\Head;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        // Livewire marks every response it renders no-store. Content pages are
        // static HTML; a nested form component switches this back off on boot.
        $this->enableBackButtonCache();

        $this->slug = $slug;

        abort_unless($this->post, 404);

        $head = app(Head::class)->forEntry($this->post);
        $blog = app(ContentRepository::class)->page('/blog');

        $head->breadcrumbs = [
            ($blog?->title ?? 'Blog') => url('/blog'),
            $this->post->title => $this->post->url(),
        ];

        // Scheduled posts are reachable before their date but not yet public.
        $head->noindex = ! $this->post->isListed();
    }

    #[Computed]
    public function post(): ?Entry
    {
        return app(ContentRepository::class)->post($this->slug);
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function tags(): array
    {
        $titles = app(ContentRepository::class)->tags();

        return collect($this->post->tags)
            ->filter(fn ($tag) => $titles->has($tag))
            ->mapWithKeys(fn ($tag) => [$tag => $titles[$tag]])
            ->all();
    }
};
?>

<article class="pt-8 md:pt-12">
    <header class="mx-auto max-w-6xl px-4 md:px-8">
        <flux:breadcrumbs class="mb-6">
            <flux:breadcrumbs.item href="/blog">Blog</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>
                <time datetime="{{ $this->post->date->toIso8601String() }}">{{ $this->post->date->format('F j, Y') }}</time>
            </flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <h1 class="max-w-4xl font-semiwide text-4xl md:text-6xl font-bold tracking-tight text-balance">{{ $this->post->title }}</h1>
        @if ($this->post->description)
            <p class="mt-5 max-w-2xl font-serif text-xl md:text-2xl leading-snug text-zinc-700 dark:text-zinc-300">{{ $this->post->description }}</p>
        @endif
        @if ($this->tags)
            <ul class="flex flex-wrap gap-2 mt-6" aria-label="Tags">
                @foreach ($this->tags as $slug => $title)
                    <li><flux:badge as="a" href="/tags/{{ $slug }}" rel="tag" size="sm">{{ $title }}</flux:badge></li>
                @endforeach
            </ul>
        @endif
        @if ($this->post->featureImage)
            <img class="mt-10 w-full max-w-4xl rounded-sm" src="{{ \App\Support\Image::url($this->post->featureImage) }}" alt="" fetchpriority="high">
        @endif
    </header>
    <x-content.body class="mt-10">
        @include($this->post->view)
    </x-content.body>
</article>
