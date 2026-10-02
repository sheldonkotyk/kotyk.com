@props(['skyTime' => null])
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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=Source+Serif+4:ital,opsz,wght@0,8..60,400..700;1,8..60,400..700&display=swap">
        <link rel="preconnect" href="https://kit.fontawesome.com" crossorigin>
        <script src="https://kit.fontawesome.com/29548d475a.js" crossorigin="anonymous" defer></script>
        @vite(['resources/css/site.css', 'resources/js/site.js'])
        @fluxAppearance
        @if ($head->livewire)
            @livewireStyles
        @else
            {{-- Livewire bundles Alpine; pages without it still need Alpine for Flux. --}}
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
    </head>
    <body class="min-h-screen bg-snow font-sans text-ink antialiased dark:bg-night dark:text-zinc-200">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-white focus:p-2 focus:text-ink">Skip to content</a>
        <x-site.header :home="request()->is('/')" :sky-time="$skyTime" />
        <main id="main">
            {{ $slot }}
        </main>
        <x-site.footer />
        @if ($head->livewire)
            @livewireScripts
        @endif
        {{-- After Livewire, and before the deferred Alpine above starts. Not
             the @fluxScripts directive: it forces Livewire's script, and its
             CSRF token, onto every page, which stops the edge caching them. --}}
        {!! app('flux')->scripts() !!}
    </body>
</html>
