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
};
?>

<div class="container px-6 mx-auto md:px-0">
    <div class="py-8">
        <h1 class="mb-8">{{ $this->page?->title ?? 'Blog' }}</h1>
        @if ($this->page)
            <div class="content">
                @include($this->page->view)
            </div>
        @endif
        @foreach ($this->posts as $post)
            <x-blog.card :post="$post" />
        @endforeach
    </div>
</div>
