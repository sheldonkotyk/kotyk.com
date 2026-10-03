{{-- A postage stamp: perforated edge, the water tower over the fields, and
     the denomination in the corner. --}}
<span {{ $attributes->class('postcard-stamp inline-block bg-white p-[3px]') }} aria-hidden="true">
    <svg viewBox="0 0 44 54" class="block w-11 h-auto">
        <rect width="44" height="54" fill="#2f7bc8" />
        <rect y="38" width="44" height="6" fill="#f2c230" />
        <rect y="44" width="44" height="10" fill="#5f9b3e" />
        <path d="M20 37 L21 22 L23 22 L24 37 Z M22 11 a7 6 0 1 0 0.1 0 Z" fill="#15202b" opacity="0.8" />
        <circle cx="34" cy="9" r="4" fill="#fff3c4" />
        <text x="4" y="10" fill="#fff" font-family="Archivo, sans-serif" font-size="7" font-weight="800">5¢</text>
        <text x="22" y="51" fill="#fff" font-family="Archivo, sans-serif" font-size="5.5" font-weight="700" text-anchor="middle" letter-spacing="0.8">CANADA</text>
    </svg>
</span>
