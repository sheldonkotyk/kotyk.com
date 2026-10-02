@props(['post', 'image' => false])
{{-- One post in a list: when it went up, what it's called, what it's about. --}}
<article class="group relative grid gap-x-8 gap-y-1 py-6 border-t border-frost dark:border-zinc-800 sm:grid-cols-[7rem_1fr]">
    @if ($post->date)
        <time class="pt-1 text-sm tabular-nums text-zinc-500 dark:text-zinc-400" datetime="{{ $post->date->toDateString() }}">{{ $post->date->format('M j, Y') }}</time>
    @endif
    <div class="sm:col-start-2">
        @if ($image && $post->image())
            <img class="mb-4 w-full rounded-sm aspect-[2/1] object-cover" src="{{ \App\Support\Image::url($post->image(), 'card') }}" alt="" loading="lazy" decoding="async">
        @endif
        <h3 class="font-semiwide text-xl font-bold leading-snug text-balance">
            <a href="{{ $post->uri }}" class="after:absolute after:inset-0 group-hover:underline decoration-canola decoration-2 underline-offset-4">{{ $post->title }}</a>
        </h3>
        <p class="mt-2 max-w-2xl font-serif text-lg leading-relaxed text-zinc-700 dark:text-zinc-300">{{ $post->summary() }}</p>
    </div>
</article>
