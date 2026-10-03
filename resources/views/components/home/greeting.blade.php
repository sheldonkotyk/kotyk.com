{{-- The intro as a postcard to the reader: a large-letter "Greetings from
     Steinbach" on one side (the letters filled with the fields under the
     sky), and the message, stamp, postmark and address on the other. --}}
<article {{ $attributes->class('postcard-paper grid overflow-hidden rounded-sm shadow-[0_1px_0_rgb(0_0_0/0.06),0_18px_40px_-24px_rgb(0_0_0/0.45)] md:grid-cols-[2fr_3fr]') }} aria-label="A postcard from Steinbach">
    <div class="postcard-greeting relative flex flex-col justify-center gap-1 px-6 py-10 text-center md:py-6">
        <p class="font-serif italic text-xl text-white drop-shadow">Greetings from</p>
        <p class="postcard-big-letters font-tall font-black uppercase leading-[0.8] text-[19vw] md:text-[6.4vw] xl:text-[5.4rem]">Steinbach</p>
        <p class="font-serif italic text-white drop-shadow">Manitoba</p>
    </div>

    <div class="relative grid gap-6 px-6 py-6 sm:grid-cols-[minmax(0,1fr)_10rem] sm:px-8 sm:py-8">
        <div class="postcard-message prose max-w-none font-serif text-[1.02rem] leading-relaxed text-ink prose-p:my-3 sm:border-e sm:border-ink/15 sm:pe-6">
            {{ $slot }}
        </div>
        <div class="flex flex-col items-end gap-5 sm:items-stretch">
            <div class="relative flex justify-end">
                <x-home.postmark class="absolute -start-10 top-6 w-44 text-[#4a3f6b]/70 -rotate-12" />
                <x-home.stamp class="rotate-2 origin-top-right scale-[1.35]" />
            </div>
            <div class="w-full space-y-3 pt-6 font-serif italic text-zinc-600 sm:pt-10">
                <p class="border-b border-ink/25 pb-1">To whoever's reading,</p>
                <p class="border-b border-ink/25 pb-1">somewhere on the internet</p>
                <p class="border-b border-ink/25 pb-1">&nbsp;</p>
            </div>
        </div>
    </div>
</article>
