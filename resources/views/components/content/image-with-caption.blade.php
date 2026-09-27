@props(['image', 'caption' => null, 'locate' => null])
@php($caption = $caption ? html_entity_decode($caption, ENT_QUOTES | ENT_HTML5) : null)
<div @class(['container mx-auto', 'w-full flex justify-center' => $locate === 'center'])>
    <figure @class([
        'rounded overflow-hidden',
        'max-w-xs md:float-left my-4 md:w-full md:mr-16' => $locate === 'left',
        'max-w-xs md:float-right my-4 md:w-full md:ml-16' => $locate === 'right',
        'w-full md:w-1/2' => $locate === 'center',
        'w-full my-8' => $locate === 'full',
    ])>
        <img class="w-full" src="{{ \App\Support\Image::url($image, in_array($locate, ['left', 'right']) ? 'small' : 'large') }}" alt="{{ $caption ?? '' }}" loading="lazy" decoding="async">
        @if ($caption)
            <figcaption class="px-6 py-4 mb-2 text-sm font-bold text-center">{{ $caption }}</figcaption>
        @endif
    </figure>
</div>
