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
     * Posts grouped by the year they went up, newest year first.
     *
     * @return Collection<int, Collection<int, Entry>>
     */
    #[Computed]
    public function years(): Collection
    {
        return $this->posts->groupBy(fn (Entry $post) => $post->date->year);
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
</div>
