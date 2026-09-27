@props(['post'])
<article class="flex items-start py-4 border-t">
    <div class="pt-[2px]">
        <h2 class="mb-3 text-lg font-bold leading-snug">
            <a href="{{ $post->uri }}" class="hover:text-gray-700">{{ $post->title }}</a>
        </h2>
        <p class="text-sm antialiased">{{ $post->summary() }}</p>
        @if ($post->date)
            <div class="flex flex-wrap mt-4 space-x-2 font-mono text-xs tracking-widest text-gray-400 uppercase md:space-x-4">
                <time datetime="{{ $post->date->toDateString() }}">{{ $post->date->format('F jS, Y') }}</time>
            </div>
        @endif
    </div>
</article>
