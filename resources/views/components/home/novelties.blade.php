@props(['experiments'])
{{-- The Midjourney experiments as novelty cards, fanned out on the rack. --}}
<div {{ $attributes }}>
    <h2 class="font-tall font-black uppercase text-3xl leading-none">Novelty cards</h2>
    <p class="mt-1 font-serif italic text-zinc-600 dark:text-zinc-400">Midjourney experiments, each with the prompt that made it</p>
    <ul class="mt-6 flex flex-wrap gap-3 sm:gap-0 sm:ps-6">
        @foreach ($experiments as $experiment)
            <li class="novelty-card sm:-ms-6" style="--tilt: {{ [-6, 3, -2, 5, -4][$loop->index % 5] }}deg">
                <a href="{{ $experiment->uri }}" class="block w-28 bg-white p-1.5 pb-2 shadow-[0_2px_10px_-2px_rgb(0_0_0/0.35)] focus-visible:outline-offset-4 sm:w-32">
                    @if ($experiment->image())
                        <img src="{{ \App\Support\Image::url($experiment->image(), 'card') }}" alt="" class="block aspect-[3/4] w-full object-cover" loading="lazy" decoding="async">
                    @endif
                    <span class="mt-1.5 block truncate text-center font-tall text-sm font-black uppercase text-ink">{{ $experiment->title }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
