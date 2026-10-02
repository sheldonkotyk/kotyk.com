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

<div class="mx-auto max-w-6xl px-4 pt-8 md:px-8 md:pt-12">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="/tags">Tags</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $title }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>
    <h1 class="font-semiwide text-4xl md:text-6xl font-bold tracking-tight">{{ $title }}</h1>
    <div class="mt-10 max-w-4xl">
        @foreach ($this->entries as $entry)
            <x-blog.card :post="$entry" />
        @endforeach
    </div>
</div>
