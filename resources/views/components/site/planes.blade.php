{{-- Small planes out of Steinbach's airfields: two crossing the sky, one in
     the middle of a loop, and now and then one coming in to land at the
     hangar. They head home as the light goes, and hold still (and stay
     parked) for anyone who prefers reduced motion. --}}
@php
    // A high-wing single, side on, nose to the right.
    $plane = '<svg viewBox="0 0 40 14" class="w-full h-auto">'
        .'<path d="M3 8 Q3 6 7 6 L26 5.4 Q34 5.4 37 7.4 Q34 9.6 26 9.6 L7 9.2 Q3 9.2 3 8 Z"/>'
        .'<path d="M3.5 6.5 L1.5 1 L6 1 L10 6.2 Z"/>'
        .'<rect x="15" y="3.4" width="13" height="1.6" rx="0.8"/>'
        .'<path d="M19 5 L16 9" stroke-width="0.8" class="sky-plane-line"/>'
        .'<rect x="37.6" y="3.6" width="0.9" height="7.8" rx="0.45"/>'
        .'<circle cx="24" cy="11.4" r="1.2"/><circle cx="34" cy="11.2" r="1"/>'
        .'</svg>';
@endphp
{{-- Up high, behind the town --}}
<div class="sky-planes absolute inset-0 z-[3] pointer-events-none">
    <div class="sky-plane sky-plane-east top-[12%] w-9" aria-hidden="true">{!! $plane !!}</div>
    <div class="sky-plane sky-plane-west top-[24%] w-7" aria-hidden="true">{!! $plane !!}</div>
    <div class="sky-plane-loop top-[30%] left-[62%]">
        {{-- Harv's Air's aerobatic "Flight of your life" --}}
        <a href="https://www.harvsair.com/products/flight-of-your-life/" target="_blank" rel="noopener"
           title="Flight of your life with Harv's Air" aria-label="Flight of your life with Harv's Air"
           class="sky-plane-stunt block box-content w-5 p-3 pointer-events-auto">{!! $plane !!}</a>
    </div>
</div>

{{-- Coming in low, in front of the town and behind the name --}}
<div class="sky-planes absolute inset-0 z-[8] pointer-events-none" aria-hidden="true">
    <div class="absolute top-0 left-0 w-8 opacity-0" data-landing-plane>{!! $plane !!}</div>
</div>

<script>
(() => {
    const plane = document.querySelector('[data-landing-plane]');
    const sky = plane.closest('[data-sky]');

    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    function land() {
        const hangar = sky.querySelector('[data-hangar]');
        const night = parseFloat(sky.style.getPropertyValue('--stars')) > 0.3;

        // Nobody flies in after dark, in bad weather, or when the hangar is off screen.
        if (! hangar || night || sky.hasAttribute('data-grounded')) return schedule();

        const box = sky.getBoundingClientRect();
        const shed = hangar.getBoundingClientRect();
        if (shed.right < box.left || shed.left > box.right) return schedule();

        const width = plane.offsetWidth, height = plane.offsetHeight;
        const ground = shed.bottom - box.top - height;
        const stop = shed.left - box.left - width * 0.4;
        const touchdown = stop - Math.max(120, box.width * 0.12);
        const start = touchdown - box.width * 0.9;
        const at = (x, y, turn) => ({ transform: `translate(${x}px, ${y}px) rotate(${turn}deg)` });

        // In on a long glide, flare, touch down, roll out, taxi into the hangar.
        plane.animate([
            { ...at(start, ground - box.height * 0.45, 5), opacity: 1 },
            { ...at(touchdown - 60, ground - 6, 4), opacity: 1, offset: 0.78 },
            { ...at(touchdown, ground, -3), opacity: 1, offset: 0.84, easing: 'ease-out' },
            { ...at(stop - 20, ground, 0), opacity: 1, offset: 0.95 },
            { ...at(stop, ground, 0), opacity: 0 },
        ], { duration: 26000, easing: 'linear', fill: 'forwards' }).finished.then(schedule);
    }

    // The first one comes in soon after the page opens, then every minute or two.
    let first = true;
    function schedule() {
        setTimeout(land, first ? 6000 : 50000 + Math.random() * 70000);
        first = false;
    }

    schedule();
})();
</script>
