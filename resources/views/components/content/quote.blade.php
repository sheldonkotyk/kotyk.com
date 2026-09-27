@props(['cite' => null])
<figure class="my-8">
    <blockquote>{{ $slot }}</blockquote>
    @if ($cite)
        <figcaption class="mt-2 text-sm text-gray-600">&mdash; <cite>{{ $cite }}</cite></figcaption>
    @endif
</figure>
