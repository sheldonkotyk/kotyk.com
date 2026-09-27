@php($head = app(\App\View\Head::class))
<!doctype html>
<html lang="{{ config('seo.language') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-site.seo :head="$head" />
        <x-site.favicons />
        <link rel="alternate" type="application/atom+xml" title="{{ config('seo.site_name') }}" href="{{ route('feed') }}">
        <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">
        <link rel="author" type="text/plain" href="{{ url('/humans.txt') }}">
        <link rel="preconnect" href="https://kit.fontawesome.com" crossorigin>
        <script src="https://kit.fontawesome.com/29548d475a.js" crossorigin="anonymous" defer></script>
        @vite(['resources/css/site.css', 'resources/js/site.js'])
        @if ($head->livewire)
            @livewireStyles
        @else
            {{-- Livewire bundles Alpine; pages without it still need Alpine for the nav. --}}
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
    </head>
    <body class="font-sans">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:p-2">Skip to content</a>
        <x-site.nav />
        <main id="main">
            {{ $slot }}
        </main>
        <x-site.footer />
        @if ($head->livewire)
            @livewireScripts
        @endif
    </body>
</html>
