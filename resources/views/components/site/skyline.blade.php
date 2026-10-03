{{-- Steinbach on the horizon, drawn in haze a step lighter than the name
     standing in front of it. The short sky on inner pages can't fit the whole
     town at full width, so there it is drawn small and repeated along the
     strip, like towns strung along a rail line. --}}
@props(['compact' => false])
@if ($compact)
    <svg {{ $attributes->class('sky-skyline absolute inset-x-0 bottom-0 w-full h-14') }} aria-hidden="true" focusable="false">
        <defs>
            <pattern id="sky-town" width="1600" height="140" patternUnits="userSpaceOnUse" patternTransform="scale(0.4)">
                @include('components.site.town')
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#sky-town)" />
    </svg>
@else
    {{-- The town is scaled by the sky's height, never its width, so it keeps
         its size beside the name on wide screens: the viewBox is three towns
         wide, and the flanking ones (houses only) fill out whatever the
         screen shows beyond the middle one. --}}
    <svg {{ $attributes->class('sky-skyline absolute inset-x-0 bottom-0 w-full') }} viewBox="-1600 0 4800 140" preserveAspectRatio="xMidYMax slice" role="group" aria-label="Steinbach">
        <g transform="translate(-1600 0)">@include('components.site.town', ['landmarks' => false])</g>
        @include('components.site.town')
        <g transform="translate(1600 0)">@include('components.site.town', ['landmarks' => false])</g>
    </svg>
@endif
