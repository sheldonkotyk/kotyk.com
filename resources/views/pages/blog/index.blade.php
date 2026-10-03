<?php

use App\Content\ContentRepository;
use App\Content\Entry;
use App\View\Head;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public function mount(): void
    {
        // Livewire marks every response it renders no-store. Content pages are
        // static HTML; a nested form component switches this back off on boot.
        $this->enableBackButtonCache();

        $head = app(Head::class);

        if ($this->page) {
            $head->forEntry($this->page);
        } else {
            $head->title = 'Blog';
            $head->canonical = url('/blog');
        }

        $head->schema[] = [
            '@type' => 'Blog',
            '@id' => url('/blog').'#blog',
            'url' => url('/blog'),
            'name' => $head->title,
            'author' => ['@id' => url('/').'#person'],
            'blogPost' => $this->posts->map(fn (Entry $post) => [
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'url' => $post->url(),
                'datePublished' => $post->date->toIso8601String(),
            ])->all(),
        ];
    }

    /**
     * The /blog page's own front matter (title, nav position, intro copy).
     */
    #[Computed]
    public function page(): ?Entry
    {
        return app(ContentRepository::class)->page('/blog');
    }

    /**
     * @return Collection<int, Entry>
     */
    #[Computed]
    public function posts(): Collection
    {
        return app(ContentRepository::class)->posts();
    }

    /**
     * The writing, grouped by the year it went up, newest year first. The
     * Midjourney experiments are kept apart, below.
     *
     * @return Collection<int, Collection<int, Entry>>
     */
    #[Computed]
    public function years(): Collection
    {
        return app(ContentRepository::class)->writing()->groupBy(fn (Entry $post) => $post->date->year);
    }

    /**
     * @return Collection<int, Entry>
     */
    #[Computed]
    public function experiments(): Collection
    {
        return app(ContentRepository::class)->experiments();
    }
};
?>

<div class="pt-10 md:pt-14">
    <header class="mx-auto max-w-6xl px-4 md:px-8">
        <h1 class="font-semiwide text-4xl md:text-6xl font-bold tracking-tight">{{ $this->page?->title ?? 'Blog' }}</h1>
        <p class="mt-4 max-w-2xl font-serif text-xl text-zinc-700 dark:text-zinc-300">Writing on leadership, faith, technology and family.</p>
    </header>
    @if ($this->page)
        <x-content.body>
            @include($this->page->view)
        </x-content.body>
    @endif

    <div class="mx-auto max-w-6xl px-4 md:px-8 mt-12">
        <flux:timeline align="start" class="max-w-4xl">
            @foreach ($this->years as $year => $posts)
                <flux:timeline.item>
                    <flux:timeline.indicator class="bg-canola! text-ink! font-sans">
                        <span class="sr-only">{{ $year }}</span>
                    </flux:timeline.indicator>
                    <flux:timeline.content>
                        <h2 class="font-semiwide text-3xl font-bold tabular-nums pt-0.5">{{ $year }}</h2>
                        <div class="mt-4 mb-10 [&>article:first-child]:border-t-0 [&>article:first-child]:pt-2">
                            @foreach ($posts as $post)
                                <x-blog.card :post="$post" />
                            @endforeach
                        </div>
                    </flux:timeline.content>
                </flux:timeline.item>
            @endforeach
        </flux:timeline>
    </div>

    @if ($this->experiments->isNotEmpty())
        {{-- Pictures rather than writing, so they get a shelf of their own --}}
        <section class="mx-auto max-w-6xl px-4 md:px-8 mt-16" aria-labelledby="experiments-title">
            <h2 id="experiments-title" class="font-semiwide text-3xl font-bold">Midjourney experiments</h2>
            <p class="mt-2 max-w-2xl font-serif text-lg text-zinc-700 dark:text-zinc-300">Pictures from Midjourney, each with the prompt that grew it.</p>
            <ul class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($this->experiments as $experiment)
                    <li>
                        <a href="{{ $experiment->uri }}" class="group block focus-visible:outline-offset-4">
                            @if ($experiment->image())
                                <img src="{{ \App\Support\Image::url($experiment->image(), 'card') }}" alt="" class="block w-full aspect-square rounded-sm object-cover" loading="lazy" decoding="async">
                            @endif
                            <span class="mt-2 block font-semiwide font-bold leading-snug group-hover:underline decoration-canola decoration-2 underline-offset-4">{{ $experiment->title }}</span>
                            <time class="block text-sm text-zinc-500 dark:text-zinc-400" datetime="{{ $experiment->date->toDateString() }}">{{ $experiment->date->format('M j, Y') }}</time>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
