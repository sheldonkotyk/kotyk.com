{{-- Today in Steinbach as a weather report card: the day the sky above is
     drawing. The sky's script fills it in (and previews it with ?time=,
     ?date= and ?weather=); without script it keeps its dashes. --}}
@php
    $rows = [
        'sunrise' => 'Sunrise',
        'sunset' => 'Sunset',
        'daylight' => 'Daylight',
        'weather' => 'Outside',
        'wind' => 'Wind',
        'aurora' => 'Northern lights',
        'planes' => 'Over the airfield',
    ];
@endphp
<aside {{ $attributes->class('postcard-paper relative rounded-sm px-6 py-6 text-ink shadow-[0_1px_0_rgb(0_0_0/0.06),0_16px_30px_-20px_rgb(0_0_0/0.5)]') }} aria-labelledby="almanac-title">
    <x-home.postmark class="absolute -end-2 -top-5 w-24 text-[#4a3f6b]/55 rotate-6" />
    <h2 id="almanac-title" class="pe-16 pt-4 font-tall font-black uppercase text-3xl leading-none text-seed-green">Wish you were here</h2>
    <p class="mt-1 font-serif italic text-sm text-zinc-600" data-almanac="date">Today in Steinbach, Manitoba</p>
    <dl class="mt-5 space-y-2 font-serif text-[0.95rem]">
        @foreach ($rows as $key => $label)
            <div class="flex items-baseline gap-2">
                <dt class="shrink-0 text-zinc-600">{{ $label }}</dt>
                <span class="flex-1 border-b border-ink/25 translate-y-[-0.25em]" aria-hidden="true"></span>
                <dd class="text-right font-semibold italic" data-almanac="{{ $key }}">—</dd>
            </div>
        @endforeach
    </dl>
</aside>
