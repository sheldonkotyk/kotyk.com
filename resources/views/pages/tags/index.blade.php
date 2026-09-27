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

<div class="relative z-10 max-w-6xl px-6 py-8 mx-auto">
    <div class="py-8">
        <h1 class="mb-6">Tags</h1>
        <ul class="flex flex-wrap gap-4 pt-8 mt-8 content">
            @foreach ($this->tags as $slug => $title)
                <li><a href="/tags/{{ $slug }}" rel="tag">{{ $title }}</a></li>
            @endforeach
        </ul>
    </div>
</div>
