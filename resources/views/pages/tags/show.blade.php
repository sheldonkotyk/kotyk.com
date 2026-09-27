<?php

use App\Content\ContentRepository;
use App\Content\Entry;
use App\View\Head;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $tag;

    public string $title;

    public function mount(string $tag): void
    {
        // Livewire marks every response it renders no-store. Content pages are
        // static HTML; a nested form component switches this back off on boot.
        $this->enableBackButtonCache();

        $this->tag = $tag;
        $this->title = app(ContentRepository::class)->tags()->get($tag) ?? abort(404);

        $head = app(Head::class);
        $head->title = $this->title;
        $head->description = "Posts and pages by Sheldon Kotyk tagged {$this->title}.";
        $head->canonical = url("/tags/{$tag}");
        $head->breadcrumbs = ['Tags' => url('/tags'), $this->title => $head->canonical];

        // Thin archive pages: let crawlers follow them to the posts, but keep
        // them out of the index so they don't compete with the posts.
        $head->noindex = true;
    }

    /**
     * @return Collection<int, Entry>
     */
    #[Computed]
    public function entries(): Collection
    {
        return app(ContentRepository::class)->tagged($this->tag);
    }
};
?>

<div class="relative z-10 max-w-6xl px-6 py-8 mx-auto">
    <div class="py-8">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm text-gray-500">
            <a href="/tags" class="hover:underline">Tags</a>
        </nav>
        <h1 class="mb-6">{{ $title }}</h1>
        @foreach ($this->entries as $entry)
            <x-blog.card :post="$entry" />
        @endforeach
    </div>
</div>
