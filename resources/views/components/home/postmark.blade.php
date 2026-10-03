@props(['date' => null])
{{-- A Steinbach postmark: the town round the ring, the date across the
     middle, and wavy cancellation lines trailing off to the left. --}}
@php($id = 'pm-'.substr(md5(($date?->toDateString() ?? 'today').random_int(0, 9999)), 0, 8))
<svg viewBox="0 0 150 64" {{ $attributes->class('sky-postmark') }} aria-hidden="true" focusable="false">
    <g fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
        @foreach ([14, 24, 34, 44] as $y)
            <path d="M2 {{ $y }} q 8 -5 16 0 t 16 0 t 16 0 t 16 0 t 16 0" />
        @endforeach
        <circle cx="116" cy="32" r="28" />
        <circle cx="116" cy="32" r="19" />
    </g>
    <defs>
        <path id="{{ $id }}" d="M 91 32 a 25 25 0 1 1 50 0" />
    </defs>
    <text fill="currentColor" font-family="Archivo, sans-serif" font-size="7.6" font-weight="700" letter-spacing="1.2">
        <textPath href="#{{ $id }}" startOffset="50%" text-anchor="middle">STEINBACH, MB</textPath>
    </text>
    <text x="116" y="35" fill="currentColor" font-family="Archivo, sans-serif" font-size="7.5" font-weight="700" text-anchor="middle">{{ strtoupper(($date ?? now())->format('M j')) }}</text>
    <text x="116" y="43" fill="currentColor" font-family="Archivo, sans-serif" font-size="6.5" text-anchor="middle">{{ ($date ?? now())->format('Y') }}</text>
</svg>
