<?php

use App\Content\ContentRepository;
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
        $head->title = 'Tags';
        $head->description = 'Every topic Sheldon Kotyk writes about, from leadership and faith to technology and humour.';
        $head->canonical = url('/tags');
        $head->breadcrumbs = ['Tags' => url('/tags')];
    }

    /**
     * @return Collection<string, string>
     */
    #[Computed]
    public function tags(): Collection
    {
        return app(ContentRepository::class)->tags();
    }
};
?>

<div class="mx-auto max-w-6xl px-4 pt-10 md:px-8 md:pt-14">
    <h1 class="font-semiwide text-4xl md:text-6xl font-bold tracking-tight">Tags</h1>
    <ul class="flex flex-wrap gap-2 mt-10 max-w-3xl">
        @foreach ($this->tags as $slug => $title)
            <li><flux:badge as="a" href="/tags/{{ $slug }}" rel="tag">{{ $title }}</flux:badge></li>
        @endforeach
    </ul>
</div>
