@props(['posts'])
{{-- The latest posts, as you'd pass them on a grid road out of town: the
     newest on a billboard with its picture, the next ones on green highway
     signs, then a blue sign to every post and a brown one to the feed. Each
     stands on its posts at the gravel shoulder. --}}
@php
    $billboard = $posts->first();
    $signs = $posts->slice(1, 4)->values();
    // Two posts down to this item's stretch of the gravel shoulder. Items sit
    // edge to edge, so each row's shoulder reads as one road.
    $post = fn ($class = 'h-10') => '<span class="flex justify-center gap-8 sm:gap-16" aria-hidden="true"><span class="block w-1.5 '.$class.' bg-zinc-400 dark:bg-zinc-600"></span><span class="block w-1.5 '.$class.' bg-zinc-400 dark:bg-zinc-600"></span></span>'
        .'<span class="-mx-3 block h-3 bg-[#cdbb96] dark:bg-[#4a4335]" aria-hidden="true"></span>';
    $sign = 'group block rounded-md p-1 shadow-sm text-white focus-visible:outline-offset-4';
    $inner = 'block rounded-[0.3rem] border-2 border-white/90 px-3 py-2.5 sm:px-4 sm:py-3';
@endphp
<section {{ $attributes->class('mx-auto max-w-6xl px-1 md:px-5') }} aria-labelledby="latest-title">
    <h2 id="latest-title" class="px-3 font-semiwide text-3xl md:text-4xl font-bold tracking-tight">The Latest</h2>

    <div class="mt-8 grid grid-cols-2 gap-y-10 lg:grid-cols-4">
        @if ($billboard)
            {{-- The billboard --}}
            <a href="{{ $billboard->uri }}" class="group flex flex-col justify-end px-3 col-span-2 focus-visible:outline-offset-4">
                <span class="block rounded-sm border-[6px] border-zinc-700 bg-zinc-700 shadow-md dark:border-zinc-600">
                    @if ($billboard->image())
                        <img src="{{ \App\Support\Image::url($billboard->image(), 'card') }}" alt="" class="block w-full aspect-[2/1] object-cover" loading="lazy" decoding="async">
                    @endif
                    <span class="block bg-white px-4 py-3 text-ink">
                        <span class="block font-semiwide text-xl md:text-2xl font-bold leading-snug group-hover:underline decoration-canola decoration-2 underline-offset-4">{{ $billboard->title }}</span>
                        <span class="mt-1 block font-serif text-sm text-zinc-600">{{ $billboard->date->format('F j, Y') }}</span>
                    </span>
                </span>
                {!! $post('h-14') !!}
            </a>
        @endif

        {{-- Green highway signs --}}
        @foreach ($signs as $item)
            <a href="{{ $item->uri }}" class="group flex flex-col justify-end px-3 focus-visible:outline-offset-4">
                <span class="{{ $sign }} bg-[#00693e]">
                    <span class="{{ $inner }}">
                        <span class="block font-semiwide text-base sm:text-lg font-bold leading-tight group-hover:underline decoration-2 underline-offset-4">{{ $item->title }}</span>
                        <span class="mt-1.5 block text-sm text-white/80">{{ $item->date->format('M j, Y') }}</span>
                    </span>
                </span>
                {!! $post() !!}
            </a>
        @endforeach

        {{-- A blue information sign, and a brown one --}}
        <a href="/blog" class="group flex flex-col justify-end px-3 focus-visible:outline-offset-4">
            <span class="{{ $sign }} bg-[#1f4e9c]">
                <span class="{{ $inner }}">
                    <span class="block font-semiwide text-base sm:text-lg font-bold leading-tight group-hover:underline decoration-2 underline-offset-4">Every post</span>
                    <span class="mt-1.5 block text-sm text-white/80">All {{ app(\App\Content\ContentRepository::class)->posts()->count() }}, newest first</span>
                </span>
            </span>
            {!! $post() !!}
        </a>
        <a href="{{ route('feed') }}" class="group flex flex-col justify-end px-3 focus-visible:outline-offset-4">
            <span class="{{ $sign }} bg-[#6b4423]">
                <span class="{{ $inner }}">
                    <span class="block font-semiwide text-base sm:text-lg font-bold leading-tight group-hover:underline decoration-2 underline-offset-4">Follow the feed</span>
                    <span class="mt-1.5 block text-sm text-white/80">New posts in your reader</span>
                </span>
            </span>
            {!! $post() !!}
        </a>
    </div>
</section>
