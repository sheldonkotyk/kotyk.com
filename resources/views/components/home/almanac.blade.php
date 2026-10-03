{{-- Growing conditions: the day the sky above is drawing, printed like the
     conditions box in a seed catalogue. The sky's script fills it in (and
     previews it with ?time=, ?date= and ?weather=); without script it keeps
     its dashes. --}}
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
<aside {{ $attributes->class('border-y-4 border-double border-seed-green bg-white/60 px-5 py-5 dark:bg-transparent') }} aria-labelledby="almanac-title">
    <h2 id="almanac-title" class="font-tall font-black uppercase text-3xl leading-none text-seed-green dark:text-[#8fc79a]">Growing conditions</h2>
    <p class="mt-1 font-serif italic text-sm text-seed-red dark:text-[#e98a80]" data-almanac="date">Today in Steinbach, Manitoba</p>
    <dl class="mt-4 space-y-2 font-serif text-[0.95rem]">
        @foreach ($rows as $key => $label)
            <div class="flex items-baseline gap-2">
                <dt class="shrink-0 text-zinc-600 dark:text-zinc-400">{{ $label }}</dt>
                <span class="flex-1 border-b-2 border-dotted border-zinc-300 translate-y-[-0.25em] dark:border-zinc-700" aria-hidden="true"></span>
                <dd class="text-right font-semibold text-ink dark:text-white" data-almanac="{{ $key }}">—</dd>
            </div>
        @endforeach
    </dl>
</aside>
