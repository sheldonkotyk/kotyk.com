{{-- Today in Steinbach: the day the sky above is drawing, read out like a
     weather station board. The sky's script fills it in (and previews it with
     ?time=, ?date= and ?weather=); without script it keeps its dashes. --}}
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
<aside {{ $attributes->class('dark rounded-sm bg-ground px-6 py-6 text-zinc-300 shadow-[0_0_0_1px_var(--color-zinc-800)]') }} aria-labelledby="almanac-title">
    <h2 id="almanac-title" class="font-tall font-black uppercase text-4xl leading-none tracking-tight text-white">Today in Steinbach</h2>
    <p class="mt-1 font-serif italic text-sm text-zinc-400" data-almanac="date">Steinbach, Manitoba</p>
    <dl class="mt-5 space-y-2.5 text-sm">
        @foreach ($rows as $key => $label)
            <div class="flex items-baseline gap-2">
                <dt class="shrink-0 text-zinc-400">{{ $label }}</dt>
                <span class="flex-1 border-b border-dotted border-zinc-700 translate-y-[-0.25em]" aria-hidden="true"></span>
                <dd class="text-right font-medium text-canola" data-almanac="{{ $key }}">—</dd>
            </div>
        @endforeach
    </dl>
</aside>
