@props(['posts', 'experiments'])
{{-- The latest writing as seed packets in a prairie seed catalogue: the
     post's picture printed on the front, its title as the variety, its
     summary as the catalogue description, and the day it went up as the
     packing date. The Midjourney experiments follow on a page of their own,
     as a catalogue's novelties, and an order form at the foot leads to every
     post, the experiments and the feed. --}}
@php
    $tints = ['bg-[#e6eedd]', 'bg-[#f5e6b0]', 'bg-[#f1d9d1]', 'bg-[#dce8f0]'];
    $flaps = ['bg-[#c9d9b8]', 'bg-[#e9d17f]', 'bg-[#e4b9ac]', 'bg-[#b9d0e0]'];
    $content = app(\App\Content\ContentRepository::class);
@endphp
<section {{ $attributes->class('mx-auto max-w-6xl px-4 md:px-8') }} aria-labelledby="catalogue-varieties">
    <div class="flex items-center gap-4">
        <span class="h-px flex-1 bg-seed-green/40" aria-hidden="true"></span>
        <h2 id="catalogue-varieties" class="font-serif italic text-xl text-seed-red dark:text-[#e98a80]">The latest writing</h2>
        <span class="h-px flex-1 bg-seed-green/40" aria-hidden="true"></span>
    </div>

    <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($posts as $post)
            <li>
                <a href="{{ $post->uri }}" class="seed-packet group block h-full {{ $tints[$loop->index % 4] }} text-ink shadow-[0_1px_0_rgb(0_0_0/0.08),0_8px_20px_-12px_rgb(0_0_0/0.35)] transition-transform hover:-translate-y-1 focus-visible:outline-offset-4">
                    {{-- The glued flap across the top of the packet --}}
                    <span class="seed-packet-flap block h-5 {{ $flaps[$loop->index % 4] }}" aria-hidden="true"></span>
                    <span class="block px-4 pb-5 pt-3">
                        <span class="seed-print block overflow-hidden border-2 border-ink/80">
                            @if ($post->image())
                                <img src="{{ \App\Support\Image::url($post->image(), 'card') }}" alt="" class="block w-full aspect-[4/3] object-cover" loading="lazy" decoding="async">
                            @else
                                {{-- No picture: a wheat sprig, printed as the catalogue would --}}
                                <svg viewBox="0 0 120 90" class="block w-full aspect-[4/3] bg-white/50 text-seed-green" fill="currentColor" aria-hidden="true">
                                    <path d="M60 86 C60 60 61 40 64 14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                    @foreach ([[62, 20, -1], [62, 20, 1], [62.6, 31, -1], [62.6, 31, 1], [61.8, 42, -1], [61.8, 42, 1], [61.2, 53, -1], [61.2, 53, 1]] as [$x, $y, $side])
                                        <ellipse cx="{{ $x + $side * 5 }}" cy="{{ $y }}" rx="3.4" ry="7" transform="rotate({{ $side * 28 }} {{ $x + $side * 5 }} {{ $y }})" />
                                    @endforeach
                                    <ellipse cx="64.4" cy="10" rx="3" ry="6.5" />
                                    <path d="M60 70 C52 64 44 64 38 68 M60 76 C68 70 78 70 84 74" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            @endif
                        </span>
                        <span class="mt-3 block font-tall font-black uppercase text-2xl leading-[0.95] text-seed-green group-hover:text-seed-red">{{ $post->title }}</span>
                        <span class="mt-2 block font-serif text-[0.95rem] leading-snug text-zinc-700">{{ \Illuminate\Support\Str::limit($post->summary(), 110) }}</span>
                        <span class="mt-3 flex items-baseline justify-between border-t border-dashed border-ink/30 pt-2 font-serif text-xs italic text-zinc-600">
                            <span>Packed {{ $post->date->format('M j, Y') }}</span>
                            <span>Free for the reading</span>
                        </span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Novelties: the Midjourney experiments, pictures rather than writing --}}
    @if ($experiments->isNotEmpty())
        <section class="mt-20" aria-labelledby="catalogue-novelties">
            <div class="text-center">
                <h2 id="catalogue-novelties" class="font-tall font-black uppercase text-4xl text-seed-green dark:text-[#8fc79a]">Novelties</h2>
                <p class="mt-1 font-serif italic text-seed-red dark:text-[#e98a80]">Midjourney experiments, grown under glass</p>
            </div>
            <ul class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($experiments as $experiment)
                    <li>
                        <a href="{{ $experiment->uri }}" class="group block focus-visible:outline-offset-4">
                            @if ($experiment->image())
                                <span class="seed-print block overflow-hidden rounded-full border-2 border-seed-green dark:border-[#8fc79a]">
                                    <img src="{{ \App\Support\Image::url($experiment->image(), 'card') }}" alt="" class="block w-full aspect-square object-cover transition-transform group-hover:scale-105" loading="lazy" decoding="async">
                                </span>
                            @endif
                            <span class="mt-2 block text-center font-tall font-black uppercase text-lg leading-tight text-ink group-hover:text-seed-red dark:text-white">{{ $experiment->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- The order form --}}
    <div class="mt-12 border-2 border-seed-green px-5 py-5 sm:px-8 dark:border-[#8fc79a]">
        <p class="font-tall font-black uppercase text-2xl text-seed-green dark:text-[#8fc79a]">Order form</p>
        <ul class="mt-3 space-y-2 font-serif">
            <li class="flex items-baseline gap-3">
                <span class="size-4 shrink-0 translate-y-0.5 border-2 border-seed-green dark:border-[#8fc79a]" aria-hidden="true"></span>
                <a href="/blog" class="link">Every post, all {{ $content->writing()->count() }} varieties</a>
                <span class="flex-1 border-b-2 border-dotted border-zinc-300 dark:border-zinc-700" aria-hidden="true"></span>
                <span class="italic text-zinc-600 dark:text-zinc-400">Free</span>
            </li>
            <li class="flex items-baseline gap-3">
                <span class="size-4 shrink-0 translate-y-0.5 border-2 border-seed-green dark:border-[#8fc79a]" aria-hidden="true"></span>
                <a href="/tags/midjourney" class="link">The Midjourney novelties, all {{ $content->experiments()->count() }}</a>
                <span class="flex-1 border-b-2 border-dotted border-zinc-300 dark:border-zinc-700" aria-hidden="true"></span>
                <span class="italic text-zinc-600 dark:text-zinc-400">Free</span>
            </li>
            <li class="flex items-baseline gap-3">
                <span class="size-4 shrink-0 translate-y-0.5 border-2 border-seed-green dark:border-[#8fc79a]" aria-hidden="true"></span>
                <a href="{{ route('feed') }}" class="link">New varieties by feed, as they come up</a>
                <span class="flex-1 border-b-2 border-dotted border-zinc-300 dark:border-zinc-700" aria-hidden="true"></span>
                <span class="italic text-zinc-600 dark:text-zinc-400">Free</span>
            </li>
        </ul>
    </div>
</section>
