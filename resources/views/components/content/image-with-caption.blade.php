@props(['image', 'caption' => null, 'locate' => null])
@php($caption = $caption ? html_entity_decode($caption, ENT_QUOTES | ENT_HTML5) : null)
<figure @class([
    'not-prose',
    'max-w-xs my-6 sm:float-left sm:me-8 sm:w-1/2' => $locate === 'left',
    'max-w-xs my-6 sm:float-right sm:ms-8 sm:w-1/2' => $locate === 'right',
    'mx-auto my-10 w-full sm:w-2/3' => $locate === 'center',
    'my-10 w-full' => ! in_array($locate, ['left', 'right', 'center']),
])>
    <img class="w-full rounded-sm" src="{{ \App\Support\Image::url($image, in_array($locate, ['left', 'right']) ? 'small' : 'large') }}" alt="{{ $caption ?? '' }}" loading="lazy" decoding="async">
    @if ($caption)
        <figcaption class="mt-2 font-sans text-sm text-zinc-600 dark:text-zinc-400">{{ $caption }}</figcaption>
    @endif
</figure>
