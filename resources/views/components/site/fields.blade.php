{{-- The fields in front of the town and behind the name: canola, wheat,
     soybeans and corn, each with its own edge against the sky and running
     down to the road from pale at the horizon to deep up close. Gravel grid
     roads divide them, running in toward the horizon, with one crossing road.
     Plants are drawn in pixels rather than a viewBox, so they keep their size
     at any width; each field is cut to shape with clip-path.

     $edge is how far the tallest crop rises above the field. Each road meets
     the horizon at $at and spreads out by --spread on its way to the bottom. --}}
@php
    $edge = 20;
    $roads = [24, 52, 74];
    $crops = ['canola', 'wheat', 'soy', 'corn'];

    // Where a road at $at% along the horizon crosses the bottom of the field.
    $bottom = fn ($at) => "calc(50% + ($at% - 50%) * var(--spread))";
    $top = "{$edge}px";
@endphp
<div {{ $attributes->class('sky-fields absolute inset-x-0 bottom-0 h-[calc(var(--field-h)+1.25rem)]') }} aria-hidden="true">
    @foreach ($crops as $i => $crop)
        @php
            $left = $roads[$i - 1] ?? null;
            $right = $roads[$i] ?? null;
            $clip = implode(', ', [
                $left === null ? '0 0' : "$left% 0",
                $right === null ? '100% 0' : "$right% 0",
                $right === null ? '100% 100%' : "$right% $top, {$bottom($right)} 100%",
                $left === null ? '0 100%' : "{$bottom($left)} 100%, $left% $top",
            ]);
        @endphp
        <svg class="crop crop-{{ $crop }} absolute inset-0 size-full" style="clip-path: polygon({{ $clip }})" focusable="false">
            <defs>
                <linearGradient id="{{ $crop }}-depth" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" class="crop-far" />
                    <stop offset="1" class="crop-near" />
                </linearGradient>
                @switch($crop)
                    @case('canola')
                        <pattern id="canola-edge" width="20" height="18" patternUnits="userSpaceOnUse">
                            <path d="M4 18 L4 7 M11 18 L11 4 M17 18 L17 8" stroke-width="1" class="crop-stem" />
                            @foreach ([[4, 6], [11, 3], [17, 7]] as [$cx, $cy])
                                <circle cx="{{ $cx - 2 }}" cy="{{ $cy }}" r="2.4" />
                                <circle cx="{{ $cx + 2 }}" cy="{{ $cy }}" r="2.4" />
                                <circle cx="{{ $cx }}" cy="{{ $cy - 2 }}" r="2.4" />
                            @endforeach
                        </pattern>
                        <pattern id="canola-rows" width="14" height="8" patternUnits="userSpaceOnUse">
                            <circle cx="3" cy="3" r="1.4" class="crop-row-fill" />
                            <circle cx="10" cy="7" r="1.4" class="crop-row-fill" />
                        </pattern>
                        @break
                    @case('wheat')
                        <pattern id="wheat-edge" width="34" height="24" patternUnits="userSpaceOnUse">
                            <g stroke-linecap="round" fill="none" class="crop-stroke">
                                <path d="M6 24 L6.5 10 M17 24 L17 8 M27 24 L26.5 11" stroke-width="1.2" />
                                <path d="M5 3 L3.5 -1 M8 3 L9.5 -1 M16 1 L14.5 -3 M18 1 L19.5 -3 M26 4 L24.5 0 M29 4 L30.5 0" stroke-width="0.8" />
                            </g>
                            <ellipse cx="6.5" cy="6.5" rx="2.4" ry="5.5" transform="rotate(-8 6.5 6.5)" />
                            <ellipse cx="17" cy="4.5" rx="2.4" ry="5.5" />
                            <ellipse cx="27" cy="7.5" rx="2.4" ry="5.5" transform="rotate(9 27 7.5)" />
                        </pattern>
                        <pattern id="wheat-rows" width="22" height="9" patternUnits="userSpaceOnUse">
                            <path d="M3 9 L4 3 M11 9 L11 2 M18 9 L17 4" stroke-width="1" stroke-linecap="round" class="crop-row" />
                        </pattern>
                        @break
                    @case('soy')
                        <pattern id="soy-edge" width="26" height="16" patternUnits="userSpaceOnUse">
                            <ellipse cx="5" cy="10" rx="6" ry="5" />
                            <ellipse cx="14" cy="7" rx="7" ry="6" />
                            <ellipse cx="22" cy="10" rx="6" ry="5" />
                            <ellipse cx="10" cy="12" rx="5" ry="4" />
                        </pattern>
                        <pattern id="soy-rows" width="12" height="10" patternUnits="userSpaceOnUse">
                            <path d="M2 2 L2 7 M8 6 L8 10" stroke-width="2" stroke-linecap="round" class="crop-row" />
                        </pattern>
                        @break
                    @case('corn')
                        <pattern id="corn-edge" width="16" height="32" patternUnits="userSpaceOnUse">
                            <g stroke-linecap="round" fill="none" class="crop-stroke">
                                <path d="M8 32 L8 6" stroke-width="2.4" />
                                <path d="M8 23 Q14 15 18 20 M8 15 Q2 8 -2 12" stroke-width="2" />
                            </g>
                            <path d="M8 6 L5.5 0.5 M8 6 L8 -1 M8 6 L10.5 0.5" stroke-width="1.2" stroke-linecap="round" class="crop-tassel" fill="none" />
                        </pattern>
                        <pattern id="corn-rows" width="10" height="12" patternUnits="userSpaceOnUse">
                            <path d="M3 2 L3 10" stroke-width="1.5" stroke-linecap="round" class="crop-row" />
                        </pattern>
                        @break
                @endswitch
            </defs>

            {{-- Patterns tile from the origin, so each edge row is moved into
                 place rather than given a y, or it shows a slice of a plant. --}}
            @php([$rowTop, $tile] = match ($crop) { 'canola' => [$edge - 8, 18], 'wheat' => [$edge - 12, 24], 'soy' => [$edge - 9, 16], 'corn' => [$edge - 20, 32] })
            @if (in_array($crop, ['wheat', 'corn']))
                <rect class="crop-back" x="-20" width="calc(100% + 20px)" height="{{ $tile }}" fill="url(#{{ $crop }}-edge)" transform="translate({{ $crop === 'corn' ? 8 : 17 }} {{ $rowTop + 3 }})" />
            @endif
            <rect width="100%" height="{{ $tile }}" fill="url(#{{ $crop }}-edge)" transform="translate(0 {{ $rowTop }})" />
            <rect y="{{ $edge }}" width="100%" height="100%" fill="url(#{{ $crop }}-depth)" />
            <rect y="{{ $edge + 4 }}" width="100%" height="100%" fill="url(#{{ $crop }}-rows)" />
        </svg>
    @endforeach

    {{-- The grid roads: narrow at the horizon, a lane wide up close --}}
    @foreach ($roads as $at)
        <div class="sky-road absolute inset-0" style="clip-path: polygon(calc({{ $at }}% - 1px) {{ $top }}, calc({{ $at }}% + 1px) {{ $top }}, calc({{ $bottom($at) }} + 7px) 100%, calc({{ $bottom($at) }} - 7px) 100%)"></div>
    @endforeach
    <div class="sky-road absolute inset-0" style="clip-path: polygon(0 calc({{ $top }} + (100% - {{ $top }}) * 0.3), 100% calc({{ $top }} + (100% - {{ $top }}) * 0.3), 100% calc({{ $top }} + (100% - {{ $top }}) * 0.38), 0 calc({{ $top }} + (100% - {{ $top }}) * 0.38))"></div>
</div>
