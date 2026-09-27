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

<div class="container px-6 mx-auto md:px-0">
    <article class="py-8">
        <header>
            <nav aria-label="Breadcrumb" class="mb-4 text-sm text-gray-500">
                <a href="/blog" class="hover:underline">Blog</a>
            </nav>
            <h1 class="mb-6">{{ $this->post->title }}</h1>
            @if ($this->post->description)
                <p class="text-lg text-gray-800">{{ $this->post->description }}</p>
            @endif
            <div class="flex flex-wrap mt-4 gap-x-3 font-mono text-xs tracking-widest text-gray-500 uppercase">
                <time datetime="{{ $this->post->date->toIso8601String() }}">{{ $this->post->date->format('F jS, Y') }}</time>
                @foreach ($this->tags as $slug => $title)
                    <a href="/tags/{{ $slug }}" rel="tag" class="hover:underline">#{{ $title }}</a>
                @endforeach
            </div>
        </header>
        @if ($this->post->featureImage)
            <img class="mt-4" src="{{ \App\Content\Entry::assetUrl($this->post->featureImage) }}" alt="" fetchpriority="high">
        @endif
        <div class="pt-8 mt-8 content">
            @include($this->post->view)
        </div>
    </article>
</div>
