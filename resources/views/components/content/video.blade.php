@props(['videoUrl', 'locate' => null, 'title' => 'Embedded video'])
<div @class([
    'not-prose',
    'max-w-xs my-6 sm:float-left sm:me-8 sm:w-1/2' => $locate === 'left',
    'max-w-xs my-6 sm:float-right sm:ms-8 sm:w-1/2' => $locate === 'right',
    'mx-auto my-10 w-full sm:w-3/4' => $locate === 'center',
    'my-10 w-full' => ! in_array($locate, ['left', 'right', 'center']),
])>
    <div class="relative overflow-hidden rounded-sm aspect-video">
        <iframe class="absolute inset-0 w-full h-full" src="{{ \App\Support\VideoEmbed::url($videoUrl) }}" title="{{ $title }}" loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
    </div>
</div>
