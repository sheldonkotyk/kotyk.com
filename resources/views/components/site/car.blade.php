{{-- An old two-tone sedan out for a drive along the crossing grid road,
     heading west. It kicks up gravel dust by day and puts its headlights on
     after dark, red taillights glowing at the back. Its wheels sit on the
     road, 30–38% of the way down the field (see fields). It drives off to It's Worth the Trip when clicked. --}}
<div {{ $attributes->class('sky-car absolute inset-x-0 bottom-0 h-[calc(var(--field-h)+1.25rem)] pointer-events-none') }}>
    <a href="https://itsworththetrip.com/" target="_blank" rel="noopener" title="It's Worth the Trip" aria-label="It's Worth the Trip"
       class="sky-car-path absolute top-[calc(20px+(100%-20px)*0.38)] -translate-y-[calc(100%-0.75rem)] -ml-3 p-3 pointer-events-auto">
        <div class="relative w-10" aria-hidden="true">
            <div class="sky-car-dust absolute left-[70%] bottom-0 w-14 h-4"></div>
            <div class="sky-car-beam absolute right-[94%] bottom-[2px] w-20 h-3"></div>
            <svg viewBox="0 0 30 12" class="sky-car-body relative block w-full h-auto">
                <path d="M1.5 9 L1.5 6.8 Q1.5 5.6 3.5 5.3 L27 5.3 Q29 5.6 29 7 L29 9 Z" class="sky-car-paint" />
                <path d="M8 5.4 L11 2.4 Q11.8 1.8 13 1.8 L20 1.8 Q21.4 1.8 22.2 2.6 L24.6 5.4 Z" class="sky-car-roof" />
                <path d="M10.2 5 L12.2 2.8 L16 2.8 L16 5 Z M17 5 L17 2.8 L20.6 2.8 L22.8 5 Z" class="sky-car-glass" />
                <rect x="0.8" y="7.4" width="1.6" height="1.5" rx="0.5" class="sky-car-chrome" />
                <rect x="28.2" y="7.4" width="1.4" height="1.5" rx="0.5" class="sky-car-chrome" />
                <circle cx="2.1" cy="6.5" r="0.7" class="sky-car-lamp" />
                <rect x="28.3" y="5.9" width="0.9" height="1.3" rx="0.4" class="sky-car-tail" />
                <circle cx="7" cy="9.2" r="2" class="sky-car-tire" />
                <circle cx="23" cy="9.2" r="2" class="sky-car-tire" />
                <circle cx="7" cy="9.2" r="0.8" class="sky-car-chrome" />
                <circle cx="23" cy="9.2" r="0.8" class="sky-car-chrome" />
            </svg>
        </div>
    </a>
</div>
