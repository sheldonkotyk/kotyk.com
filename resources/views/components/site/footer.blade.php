@php
    $social = [
        ['href' => config('seo.social.x'), 'label' => 'X', 'icon' => 'fa-brands fa-x-twitter', 'rel' => 'me'],
        ['href' => config('seo.social.instagram'), 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'rel' => 'me'],
        ['href' => config('seo.social.github'), 'label' => 'GitHub', 'icon' => 'fa-brands fa-github', 'rel' => 'me'],
        ['href' => config('seo.social.linkedin'), 'label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin', 'rel' => 'me'],
        ['href' => '/contact', 'label' => 'Contact', 'icon' => 'fa-light fa-envelope', 'rel' => null],
    ];
@endphp
<footer class="mt-24 border-t border-frost dark:border-zinc-800">
    <div class="flex flex-wrap items-center justify-between gap-x-8 gap-y-6 mx-auto max-w-6xl px-4 py-10 md:px-8">
        <div class="flex items-center gap-1 -ms-2">
            @foreach ($social as $link)
                <flux:tooltip :content="$link['label']">
                    <flux:button variant="ghost" size="sm" square href="{{ $link['href'] }}" :rel="$link['rel']" aria-label="{{ $link['label'] }}">
                        <i class="{{ $link['icon'] }} text-base" aria-hidden="true"></i>
                    </flux:button>
                </flux:tooltip>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
            <flux:radio.group x-data variant="segmented" size="sm" x-model="$flux.appearance" aria-label="Colour theme">
                <flux:radio value="light" icon="sun" aria-label="Light" />
                <flux:radio value="dark" icon="moon" aria-label="Dark" />
                <flux:radio value="system" icon="computer-desktop" aria-label="Match system" />
            </flux:radio.group>
            {{-- Apple's mark and legal link, which its terms ask for wherever its
                 weather shows. Always here and served from this site, so it
                 doesn't wait on the sky's script or on Apple's servers, which
                 Firefox's tracking protection and content blockers can stop. --}}
            <a href="https://developer.apple.com/weatherkit/data-source-attribution/" class="flex items-center gap-1.5 text-sm text-zinc-500 hover:text-ink dark:text-zinc-400 dark:hover:text-white" target="_blank" rel="noopener">
                <span>Sky weather from</span>
                <img src="/images/apple-weather/mark-black.png" class="h-3.5 w-auto dark:hidden" width="231" height="42" alt="Apple Weather">
                <img src="/images/apple-weather/mark-white.png" class="hidden h-3.5 w-auto dark:block" width="231" height="42" alt="Apple Weather">
            </a>
            {{-- NOAA's data is public domain; this is a courtesy, shown while
                 the sky has an aurora in it. --}}
            <a href="https://www.swpc.noaa.gov/" class="text-sm text-zinc-500 hover:text-ink dark:text-zinc-400 dark:hover:text-white" target="_blank" rel="noopener" data-aurora-credit hidden>Aurora from NOAA SWPC</a>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">&copy; {{ now()->year }} Sheldon Kotyk</p>
        </div>
    </div>
</footer>
