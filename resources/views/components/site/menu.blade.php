@props(['items', 'current'])
{{-- The site's navigation, served as a diner menu. Pages in the nav that
     aren't on the card yet land under Mains with no description. --}}
@php
    $dishes = [
        '/blog' => ['Mains', 'Leadership, faith, technology and family, with the odd goose.', 'Free'],
        '/now' => ['Mains', 'The day job at Power to Change, and Abigah Co. on the side.', 'Free'],
        '/about' => ['Sides', 'Julie, Kristin, Ava and Jamie, Boston the dog, and the cats.', 'Free'],
        '/livestream' => ['Sides', 'Quest with Kirk Durston: faith, science and philosophy, live on YouTube.', 'Weekly'],
        '/contact' => ['Ask your server', 'Send a note back to the kitchen.', 'Ask'],
    ];
    $sections = $items->groupBy(fn ($item) => $dishes[$item->uri][0] ?? 'Mains')
        ->sortBy(fn ($dishes, $section) => array_search($section, ['Mains', 'Sides', 'Ask your server']));
@endphp
<div class="flex items-center">
<flux:modal.trigger name="site-menu">
    <flux:button variant="ghost" icon="book-open" class="-me-2">Menu</flux:button>
</flux:modal.trigger>

<flux:modal name="site-menu" class="w-full max-w-md bg-white! text-ink! p-0! overflow-hidden" aria-label="Site menu">
    <div class="border-[6px] border-double border-ink/80 m-2 px-6 py-8 sm:px-8">
        <header class="text-center">
            <p class="font-tall font-black uppercase text-6xl leading-none tracking-tight">Menu</p>
            <p class="mt-2 font-serif italic text-zinc-600">Served fresh in Steinbach, Manitoba</p>
        </header>

        <nav aria-label="Main" class="mt-8 space-y-8">
            @foreach ($sections as $section => $sectionItems)
                <section>
                    <h2 class="font-semiwide text-xs font-bold uppercase tracking-[0.2em] text-center text-zinc-500 border-y border-zinc-200 py-1.5">{{ $section }}</h2>
                    <ul class="mt-4 space-y-4">
                        @foreach ($sectionItems as $item)
                            @php([$_, $description, $price] = $dishes[$item->uri] ?? [null, null, null])
                            <li>
                                <a href="{{ $item->uri }}" @if ($current($item)) aria-current="page" @endif class="group block rounded-sm focus-visible:outline-offset-4">
                                    <span class="flex items-baseline gap-2">
                                        <span class="font-semiwide text-lg font-bold group-hover:underline decoration-canola decoration-2 underline-offset-4">{{ $item->title }}</span>
                                        @if ($current($item))
                                            <span class="rounded-sm bg-canola px-1.5 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-ink">You're here</span>
                                        @endif
                                        <span class="flex-1 border-b-2 border-dotted border-zinc-300 translate-y-[-0.3em]" aria-hidden="true"></span>
                                        @if ($price)
                                            <span class="font-serif italic text-zinc-700">{{ $price }}</span>
                                        @endif
                                    </span>
                                    @if ($description)
                                        <span class="block mt-0.5 font-serif text-[0.95rem] leading-snug text-zinc-600">{{ $description }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </nav>

        <p class="mt-10 text-center font-serif text-sm italic text-zinc-500">No substitutions. Gratuity not expected.</p>
    </div>
</flux:modal>

{{-- The menu needs script to open; without it, plain links. --}}
<noscript>
    <nav aria-label="Main" class="flex flex-wrap gap-x-4 text-sm">
        @foreach ($items as $item)
            <a href="{{ $item->uri }}" class="hover:text-white">{{ $item->title }}</a>
        @endforeach
    </nav>
</noscript>
</div>
