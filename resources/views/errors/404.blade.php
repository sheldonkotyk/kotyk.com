@php
    $head = app(\App\View\Head::class);
    $head->title = 'Page not found';
    $head->description = 'Either we lost this page or you lost your way.';
    $head->noindex = true;
@endphp
{{-- Always half past midnight here: the page is lost in the dark. --}}
<x-layouts.app sky-time="0.5">
    <div class="mx-auto max-w-6xl px-4 pt-10 md:px-8 md:pt-14">
        <h1 class="font-semiwide text-4xl md:text-6xl font-bold tracking-tight">This is a 404</h1>
        <p class="mt-5 max-w-2xl font-serif text-xl md:text-2xl text-zinc-700 dark:text-zinc-300">Either we lost this page or you lost your way. Which is it?</p>
        <div class="flex flex-wrap gap-3 mt-10">
            <flux:button variant="primary" href="/">Head home</flux:button>
            <flux:button href="/blog">Read the blog</flux:button>
        </div>
    </div>
</x-layouts.app>
