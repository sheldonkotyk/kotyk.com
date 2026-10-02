@props(['cite' => null])
<figure class="not-prose my-12 border-s-4 border-canola ps-6 md:-ms-7">
    <blockquote class="font-serif text-2xl md:text-3xl leading-snug text-ink dark:text-white">{{ $slot }}</blockquote>
    @if ($cite)
        <figcaption class="mt-3 font-sans text-sm text-zinc-600 dark:text-zinc-400"><cite class="not-italic">{{ $cite }}</cite></figcaption>
    @endif
</figure>
