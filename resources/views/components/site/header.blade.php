@props(['home' => false, 'skyTime' => null])
@php
    $items = app(\App\Content\ContentRepository::class)->navigation();
    $path = '/'.request()->path();
    $current = fn ($item) => $path === $item->uri || str_starts_with($path, $item->uri.'/');
@endphp
<header>
    <x-site.sky :home="$home" :time="$skyTime">
        {{-- The name stands on the horizon in the ground's own colour, like a
             town's name painted on its water tower. --}}
        @if ($home)
            <p class="sky-name font-tall font-black uppercase leading-[0.76] tracking-[-0.01em] text-[25vw] md:text-[12vw] xl:text-[9.5rem] translate-y-[0.02em]" aria-hidden="true">
                Sheldon<br class="md:hidden"> Kotyk
            </p>
        @else
            <a href="/" class="sky-name pointer-events-auto font-tall font-black uppercase leading-[0.76] tracking-[-0.01em] text-5xl md:text-6xl translate-y-[0.02em] focus-visible:outline-offset-8">
                Sheldon Kotyk
            </a>
        @endif
    </x-site.sky>

    <div class="dark bg-ground text-zinc-300">
        <div class="flex items-center justify-between gap-6 mx-auto max-w-6xl min-h-14 px-4 md:px-8">
            @if ($home)
                <p class="font-serif italic text-sm text-zinc-400 py-3" data-sky-clock></p>
            @else
                <span></span>
            @endif

            <x-site.menu :items="$items" :current="$current" />
        </div>
    </div>
</header>
