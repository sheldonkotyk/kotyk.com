@props(['videoUrl', 'locate' => null, 'title' => 'Embedded video'])
<div @class(['w-full flex justify-center md:px-48' => $locate === 'center'])>
    <div @class([
        'overflow-hidden',
        'max-w-xs md:float-left my-16 mb-8 md:w-full md:mr-16 md:flow-root' => $locate === 'left',
        'max-w-xs md:float-right my-0 mb-8 md:w-full md:ml-16 md:flow-root' => $locate === 'right',
        'w-full md:w-3/4' => $locate === 'center',
        'w-full mb-16' => $locate === 'full',
    ])>
        <div class="relative aspect-video">
            <iframe class="absolute inset-0 w-full h-full" src="{{ \App\Support\VideoEmbed::url($videoUrl) }}" title="{{ $title }}" loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        </div>
    </div>
</div>
