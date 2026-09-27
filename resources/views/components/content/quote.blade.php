@props(['cite' => null])
<figure class="not-prose w-4/5 p-8 m-auto mb-4 text-center border rounded-sm shadow-sm bg-gray-50 font-brand">
    <blockquote>{{ $slot }}</blockquote>
    @if ($cite)
        <figcaption class="pt-2 font-sans text-sm text-gray-900">&mdash; <cite>{{ $cite }}</cite></figcaption>
    @endif
</figure>
