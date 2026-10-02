{{-- The text column every page and post body sits in: the site container, set
     in Tailwind Typography. Content blocks inside opt out with not-prose. --}}
<div {{ $attributes->class('mx-auto max-w-6xl px-4 md:px-8') }}>
    <div class="prose prose-lg md:prose-xl dark:prose-invert max-w-2xl prose-headings:font-bold prose-h2:text-3xl md:prose-h2:text-4xl prose-h3:text-xl md:prose-h3:text-2xl prose-img:rounded-sm">
        {{ $slot }}
    </div>
</div>
