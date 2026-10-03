@props(['posts'])
{{-- The latest writing as photo postcards. Hover or focus one and it turns
     over to the back: the opening lines as the message, a stamp, and a
     Steinbach postmark dated the day it went up. Where there's no hover (a
     phone), the back sits under the front instead. --}}
<ul {{ $attributes->class('grid gap-x-8 gap-y-10 md:grid-cols-2') }}>
    @foreach ($posts as $post)
        <li>
            <a href="{{ $post->uri }}" class="postcard group block focus-visible:outline-offset-4" style="--tilt: {{ [-1.2, 0.8, 1.1, -0.7][$loop->index % 4] }}deg">
                <span class="postcard-inner">
                    {{-- Front --}}
                    <span class="postcard-face postcard-front block bg-white p-3 shadow-[0_1px_0_rgb(0_0_0/0.06),0_16px_30px_-20px_rgb(0_0_0/0.5)]">
                        <span class="relative block h-full overflow-hidden">
                            @if ($post->image())
                                <img src="{{ \App\Support\Image::url($post->image(), 'card') }}" alt="" class="postcard-photo block size-full object-cover" loading="lazy" decoding="async">
                            @else
                                {{-- No picture: the prairie, as a printed view card --}}
                                <span class="postcard-scene block size-full" aria-hidden="true"></span>
                            @endif
                            <span class="absolute inset-x-0 bottom-0 bg-linear-to-t from-black/70 to-transparent px-4 pb-3 pt-10">
                                <span class="block font-tall font-black uppercase text-3xl leading-[0.9] text-white">{{ $post->title }}</span>
                            </span>
                        </span>
                    </span>

                    {{-- Back --}}
                    <span class="postcard-face postcard-back postcard-paper block p-5 shadow-[0_1px_0_rgb(0_0_0/0.06),0_16px_30px_-20px_rgb(0_0_0/0.5)]">
                        <span class="grid h-full grid-cols-[minmax(0,1fr)_5rem] gap-3 sm:grid-cols-[minmax(0,1fr)_7.5rem] sm:gap-4">
                            <span class="flex flex-col border-e border-ink/15 pe-4">
                                <span class="font-serif text-[0.98rem] leading-relaxed text-ink">{{ $post->opening ?: $post->summary() }}</span>
                                <span class="mt-auto pt-3 font-semiwide text-sm font-bold text-seed-red group-hover:underline">Read the rest</span>
                            </span>
                            <span class="relative flex flex-col items-end">
                                <x-home.stamp class="rotate-3" />
                                <x-home.postmark :date="$post->date" class="absolute -start-4 top-12 w-28 text-[#4a3f6b]/75 -rotate-[14deg] sm:-start-14 sm:top-10 sm:w-40" />
                                <span class="mt-auto w-full space-y-2.5 font-serif text-xs italic text-zinc-600">
                                    <span class="block border-b border-ink/25 pb-0.5">To you, the reader</span>
                                    <span class="block border-b border-ink/25 pb-0.5">kotyk.com</span>
                                </span>
                            </span>
                        </span>
                    </span>
                </span>
            </a>
        </li>
    @endforeach
</ul>
