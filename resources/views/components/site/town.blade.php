{{-- The town itself, 1600 × 140 with the ground at the bottom: houses, City
     Hall, the event centre's arched roof, a couple of apartment blocks, the
     water tower on its pedestal, Abe's Hill and its light, the airfield's
     hangar and windsock, and a couple of grain bins. The
     windows light up as the stars come out, and the tall ones carry red
     warning lights. Shared by both sizes of sky.
     Without $landmarks it is just a run of houses, to fill out wide screens. --}}
@php
    $landmarks ??= true;
    $base = 140;
    // [x, width, wall height, roof height, chimney]
    $houses = [
        [16, 40, 21, 13, false], [150, 36, 19, 12, true], [194, 52, 26, 16, false], [254, 38, 20, 13, false],
        [460, 40, 21, 13, true], [608, 42, 22, 14, true], [658, 36, 18, 12, false], [702, 54, 27, 17, false],
        [964, 40, 21, 13, true], [1056, 52, 25, 16, false], [1116, 38, 20, 13, true], [1418, 44, 23, 15, false], [1470, 36, 19, 12, true], [1514, 40, 21, 13, false],
    ];
    // Wide screens show the edges of a town either side: just a run of houses.
    if (! $landmarks) {
        $houses = [];
        for ($x = 10, $i = 0; $x < 1580; $i++) {
            $w = 34 + ($i * 7) % 22;
            $houses[] = [$x, $w, 17 + ($i * 5) % 11, 11 + ($i * 3) % 7, $i % 3 === 0];
            $x += $w + 8 + ($i * 11) % 14;
        }
    }

    // The water tower stands in the left margin; the hill and airfield in the right.
    $tower = 104;
    $bins = [1552, 1590];
    $windows = [[522, 90], [544, 90], [522, 106], [544, 106], [533, 122], [1012, 100], [1024, 116]];
@endphp
<g class="sky-skyline-land">
    @foreach ($houses as [$x, $w, $wall, $roof, $chimney])
        @php($eave = $base - $wall)
        @if ($chimney)
            <rect x="{{ $x + $w * 0.68 }}" y="{{ $eave - $roof * 0.9 }}" width="5" height="{{ $roof }}" />
        @endif
        <polygon points="{{ $x }},{{ $base }} {{ $x }},{{ $eave }} {{ $x + $w / 2 }},{{ $eave - $roof }} {{ $x + $w }},{{ $eave }} {{ $x + $w }},{{ $base }}" />
    @endforeach

    @if ($landmarks)
        {{-- City Hall: long and low under its overhanging roof, with the stone block --}}
        <rect x="298" y="117" width="128" height="23" />
        <rect x="292" y="113" width="140" height="5" />
        <rect x="334" y="100" width="24" height="14" />

        {{-- Apartment blocks --}}
        <rect x="514" y="82" width="46" height="58" />
        <rect x="1004" y="92" width="34" height="48" />

        {{-- The event centre: the arena's arched roof and its lower wing --}}
        <path d="M760 {{ $base }} L760 98 Q830 72 900 98 L900 {{ $base }} Z" />
        <rect x="900" y="114" width="52" height="26" />

        {{-- The water tower: a round tank on one tapered pedestal --}}
        <path d="M{{ $tower - 6 }} 56 L{{ $tower + 6 }} 56 L{{ $tower + 9 }} 124 Q{{ $tower + 12 }} 136 {{ $tower + 22 }} {{ $base }} L{{ $tower - 22 }} {{ $base }} Q{{ $tower - 12 }} 136 {{ $tower - 9 }} 124 Z" />
        <ellipse cx="{{ $tower }}" cy="36" rx="27" ry="24" />

        {{-- Abe's Hill, the toboggan hill in Barkman Park, with its light --}}
        <path d="M1176 {{ $base }} C1200 {{ $base }} 1214 104 1240 104 C1266 104 1280 {{ $base }} 1304 {{ $base }} Z" class="sky-hill" />
        <rect x="1239" y="84" width="2" height="21" />
        <circle cx="1240" cy="83" r="9" class="sky-lamp-glow" />
        <circle cx="1240" cy="83" r="3" class="sky-lamp" />

        {{-- The airfield: a round-roofed hangar and its windsock. Planes land
             here; the landing script finds it by data-hangar. --}}
        <path d="M1314 {{ $base }} L1314 110 Q1342 80 1370 110 L1370 {{ $base }} Z" data-hangar />
        <rect x="1380" y="98" width="2" height="42" />
        <polygon points="1382,99 1398,101 1398,105 1382,107" class="sky-windsock" />

        {{-- Grain bins --}}
        @foreach ($bins as $x)
            <polygon points="{{ $x }},{{ $base }} {{ $x }},102 {{ $x + 18 }},88 {{ $x + 36 }},102 {{ $x + 36 }},{{ $base }}" />
        @endforeach
    @endif

    <rect x="0" y="{{ $base - 2 }}" width="1600" height="2" />
</g>

<g class="sky-skyline-windows">
    @foreach ($houses as $i => [$x, $w, $wall])
        @if ($i % 3 !== 1)
            <rect x="{{ $x + $w / 2 - 3 }}" y="{{ $base - $wall * 0.7 }}" width="6" height="6" />
        @endif
    @endforeach
    @if ($landmarks)
        @foreach ($windows as [$x, $y])
            <rect x="{{ $x }}" y="{{ $y }}" width="7" height="6" />
        @endforeach
    @endif
</g>

{{-- Red warning lights for the planes on the tallest things in town: the
     water tower and the apartment blocks. --}}
@if ($landmarks)
    <g class="sky-hazards">
        @foreach ([[$tower, 11], [537, 80], [1021, 90]] as [$x, $y])
            <circle cx="{{ $x }}" cy="{{ $y }}" r="3.2" />
        @endforeach
    </g>
@endif
