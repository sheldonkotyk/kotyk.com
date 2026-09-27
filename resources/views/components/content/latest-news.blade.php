@props(['numberToShow' => 3, 'display' => 'vertical', 'cardsPerRow' => 3])
@php
    $posts = app(\App\Content\ContentRepository::class)->posts()->take($numberToShow);
    $width = match ((int) $cardsPerRow) {
        1 => 'md:w-full', 2 => 'md:w-1/2', 3 => 'md:w-1/3', 4 => 'md:w-1/4', 5 => 'md:w-1/5', default => 'md:w-1/6',
    };
@endphp
<div class="not-prose container mx-auto">
    @if ($posts->isEmpty())
        <p>There are no blog entries published.</p>
    @elseif ($display === 'horizontal')
        @foreach ($posts as $post)
            <article class="flex-wrap w-full mb-16 md:flex">
                <div class="w-full pr-8 mx-auto md:w-1/3">
                    @if ($post->image())
                        <img src="{{ \App\Support\Image::url($post->image(), 'card') }}" class="w-full object-cover" style="max-height: 190px" alt="" loading="lazy" decoding="async">
                    @endif
                </div>
                <div class="w-full card md:w-2/3">
                    <h3 class="text-xl font-bold"><a href="{{ $post->uri }}">{{ $post->title }}</a></h3>
                    <time class="block mb-4" datetime="{{ $post->date->toDateString() }}">{{ $post->date->format('F jS, Y') }}</time>
                    <p class="text-base text-left text-gray-700">{{ $post->summary() }}</p>
                    <div class="my-8 text-left">
                        <a class="button-outline" href="{{ $post->uri }}">Read More<span class="sr-only"> about {{ $post->title }}</span></a>
                    </div>
                </div>
            </article>
        @endforeach
    @else
        <div class="mt-8 font-brand">
            <div class="flex flex-col md:flex-row md:gap-8">
                @foreach ($posts as $post)
                    <article class="w-full {{ $width }} border border-gray-200 rounded-md shadow-md overflow-hidden">
                        @if ($post->image())
                            <a href="{{ $post->uri }}" tabindex="-1" aria-hidden="true">
                                <img class="w-full" src="{{ \App\Support\Image::url($post->image(), 'card') }}" alt="" loading="lazy" decoding="async">
                            </a>
                        @endif
                        <div class="px-2 pt-4 pb-2">
                            <h3><a href="{{ $post->uri }}" class="text-lg font-black no-underline">{{ $post->title }}</a></h3>
                            <time class="block mb-4 font-mono text-xs" datetime="{{ $post->date->toDateString() }}">{{ $post->date->format('F jS, Y') }}</time>
                            <a href="{{ $post->uri }}" class="text-sm font-bold text-red-800 hover:underline">Read More<span class="sr-only"> about {{ $post->title }}</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
