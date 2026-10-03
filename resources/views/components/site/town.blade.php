{{-- The town itself, 1600 × 140 with the ground at the bottom: houses, City
     Hall, the event centre's arched roof, a couple of apartment blocks, the
     water tower on its pedestal, the Steinbach Credit Union, Abe's Hill and
     its light, the airfield's
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
        [460, 40, 21, 13, true], [608, 42, 22, 14, true], [658, 36, 18, 12, false],
        [1124, 38, 20, 13, true], [1418, 44, 23, 15, false], [1470, 36, 19, 12, true], [1514, 40, 21, 13, false],
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
    // The credit union stands on Main Street to the right of the event centre,
    // with a small building between them.
    $creditUnion = 996;
    $windows = [[522, 90], [544, 90], [522, 106], [544, 106], [533, 122], [708, 100], [720, 116], [962, 125], [976, 125]];
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
        <rect x="700" y="92" width="34" height="48" />

        {{-- The small building between the event centre and the credit union --}}
        <rect x="956" y="121" width="34" height="19" />
        <rect x="955" y="119" width="36" height="2.5" />


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

{{-- The Southeast Event Centre: the arena's arched roof and its lower wing,
     lit up on a game night (a band of windows along the arena wall, the
     wing's windows, the sign over the doors). It links to the centre, so
     unlike the rest of the town it takes clicks. --}}
@if ($landmarks)
    <a href="https://southeasteventcentre.ca/" target="_blank" rel="noopener" class="sky-town-link" aria-label="Southeast Event Centre">
        <title>Southeast Event Centre</title>
        <g class="sky-skyline-land">
            <path d="M760 {{ $base }} L760 98 Q830 72 900 98 L900 {{ $base }} Z" />
            <rect x="900" y="114" width="52" height="26" />
        </g>
        <g class="sky-skyline-windows">
            @for ($x = 770; $x <= 882; $x += 14)
                <rect x="{{ $x }}" y="118" width="9" height="5" />
            @endfor
            @foreach ([908, 922, 936] as $x)
                <rect x="{{ $x }}" y="121" width="8" height="6" />
            @endforeach
            <rect x="812" y="104" width="36" height="6" rx="1" />
        </g>
    </a>
@endif

{{-- The Steinbach Credit Union on Main Street, the tallest building in town,
     to the right of the event centre with a small building between them: the low podium and its canopy, the curved
     glass wing, the main glass block, the white stone fins rising past the
     roof, and the stair tower. After dark the glass lights up in bands and
     the red sign over the entrance glows. It links to the credit union, so it
     takes clicks. --}}
@if ($landmarks)
    <a href="https://scu.mb.ca/" target="_blank" rel="noopener" class="sky-town-link" aria-label="Steinbach Credit Union">
        <title>Steinbach Credit Union</title>
        {{-- Drawn at 1412–1533 and moved in beside the event centre --}}
        <g transform="translate({{ $creditUnion - 1412 }} 0)">
        <g class="sky-skyline-land">
            <rect x="1412" y="126" width="30" height="2" />
            <rect x="1416" y="128" width="26" height="12" />
            <path d="M1440 {{ $base }} L1440 92 Q1441 80 1456 78 L1478 77 L1478 {{ $base }} Z" />
            <rect x="1478" y="76" width="26" height="64" />
            <rect x="1512" y="72" width="18" height="68" />
        </g>
        <g class="sky-scu-fins">
            <rect x="1504" y="64" width="3.5" height="76" />
            <rect x="1508" y="66" width="3.5" height="74" />
            <rect x="1530" y="76" width="3" height="64" />
        </g>
        <g class="sky-skyline-windows">
            @foreach ([84, 93, 102, 111, 120] as $y)
                <rect x="{{ $y < 90 ? 1452 : 1446 }}" y="{{ $y }}" width="{{ $y < 90 ? 24 : 30 }}" height="4" />
            @endforeach
            @for ($y = 82; $y <= 122; $y += 9)
                @unless ($y === 100)
                    <rect x="1481" y="{{ $y }}" width="9" height="5" />
                    <rect x="1492" y="{{ $y }}" width="9" height="5" />
                @endunless
                <rect x="1515" y="{{ $y - 4 }}" width="12" height="5" />
            @endfor
        </g>
        <rect x="1483" y="104" width="16" height="5" rx="1" class="sky-scu-sign" />
        </g>
    </a>
@endif

{{-- Red warning lights for the planes on the tallest things in town: the
     water tower, the apartment blocks and the credit union. --}}
@if ($landmarks)
    <g class="sky-hazards">
        @foreach ([[$tower, 11], [537, 80], [717, 90], [$creditUnion + 94, 61]] as [$x, $y])
            <circle cx="{{ $x }}" cy="{{ $y }}" r="3.2" />
        @endforeach
    </g>
@endif
