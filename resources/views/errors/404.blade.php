@php
    $head = app(\App\View\Head::class);
    $head->title = 'Page not found';
    $head->description = 'Either we lost this page or you lost your way.';
    $head->noindex = true;
@endphp
<x-layouts.app>
    <div class="relative z-10 max-w-6xl px-6 mx-auto md:py-16">
        <div class="flex-1 py-8">
            <h1 class="mb-6">This is a 404</h1>
            <p class="text-lg text-gray-500">Either we lost this page or you lost your way. Which is it?</p>
            <p class="mt-8"><a href="/" class="underline">Head home</a> or <a href="/blog" class="underline">read the blog</a>.</p>
        </div>
    </div>
</x-layouts.app>
