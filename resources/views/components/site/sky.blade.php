@props(['home' => false, 'time' => null])
{{-- The sky over Steinbach, Manitoba, as it is right now. An inline script
     places the sun from its real elevation and colours the sky to match, so
     the page never paints a noon sky at midnight. Pages are cached at the
     edge, which is why this runs in the browser rather than on the server.
     $time pins the sky for pages that tell their own story (the 404 is
     always night); ?time=19:05 and ?date=2026-06-21 preview another hour or
     day. The weather comes from /weather.json (aroundfor.com, once an hour)
     and ?weather=rain previews it: clear, cloudy, overcast, rain, storm, snow,
     fog, windy or aurora (which shows only after dark: add &time=23). The town and the name stand at the far edge of the fields,
     --field-h deep. --}}
<div @class(['sky relative overflow-hidden', 'sky-home' => $home]) data-sky @if ($time) data-sky-time="{{ $time }}" @endif
     style="--field-h: {{ $home ? 'clamp(1.75rem, 3.5vw, 3rem)' : '1rem' }}; --spread: {{ $home ? 1.25 : 1.06 }}">
    <div class="sky-stars absolute inset-0" aria-hidden="true"></div>
    <div class="sky-aurora absolute inset-x-0 bottom-(--field-h) h-3/5" aria-hidden="true"></div>
    <div @class(['sky-sun absolute z-0 rounded-full', 'size-14 md:size-20' => $home, 'size-6' => ! $home]) aria-hidden="true"></div>
    @if ($home)
        <x-site.planes />
    @endif
    <div class="absolute inset-0 z-[4] pointer-events-none" data-sky-clouds aria-hidden="true"></div>
    <x-site.skyline :compact="! $home" @class(['z-[5] bottom-(--field-h)! pointer-events-none', 'h-[clamp(4.5rem,9vw,8rem)]' => $home]) />
    <x-site.fields class="z-[7] pointer-events-none" />
    @if ($home)
        <x-site.car class="z-[8]" />
        {{-- Above the name's layer, which spans the fields, so he can be clicked --}}
        <x-site.runner class="z-[11]" />
    @endif
    <div class="sky-fog absolute inset-x-0 bottom-0 h-3/4 z-[9] pointer-events-none" aria-hidden="true"></div>
    <div class="sky-rain absolute z-[9] pointer-events-none" aria-hidden="true"></div>
    <div class="sky-snow absolute z-[9] pointer-events-none" aria-hidden="true"></div>
    <div class="sky-flash absolute inset-0 z-[9] pointer-events-none" aria-hidden="true"></div>
    <div @class([
        // Behind the town (z-5) and the fields, in front of the sky and the
        // planes up high, so the buildings stand in front of the name. It
        // spans the whole sky, so it lets clicks through; only the name link
        // takes them.
        'relative z-[4] flex items-end mx-auto max-w-6xl px-4 pb-(--field-h) md:px-8 pointer-events-none',
        'min-h-[52svh] md:min-h-[60svh]' => $home,
        'h-28 md:h-32' => ! $home,
    ])>
        {{ $slot }}
    </div>
</div>
<script>
(() => {
    const sky = document.currentScript.previousElementSibling;
    const LAT = 49.525, LON = -96.684, rad = Math.PI / 180;

    // Sky colours by the sun's elevation in degrees: [elevation, top, bottom].
    const stops = [
        [-18, '#060b1c', '#22305c'],
        [-10, '#111d45', '#34407a'],
        [-4, '#283a78', '#b9707a'],
        [0, '#4569ab', '#f0a768'],
        [6, '#4c88c8', '#f4d6a6'],
        [15, '#3a85cf', '#bfe0f5'],
        [40, '#2f7bc8', '#d6ecfa'],
    ];

    const mix = (a, b, t) => '#' + [1, 3, 5].map(i => {
        const x = parseInt(a.slice(i, i + 2), 16), y = parseInt(b.slice(i, i + 2), 16);
        return Math.round(x + (y - x) * t).toString(16).padStart(2, '0');
    }).join('');

    function winnipegNow(at = new Date()) {
        const parts = Object.fromEntries(new Intl.DateTimeFormat('en-CA', {
            timeZone: 'America/Winnipeg', year: 'numeric', month: 'numeric', day: 'numeric',
            hour: 'numeric', minute: 'numeric', hourCycle: 'h23', timeZoneName: 'shortOffset',
        }).formatToParts(at).map(p => [p.type, p.value]));

        const offset = parseInt(parts.timeZoneName.replace('GMT', '')) || -6;
        const start = Date.UTC(+parts.year, 0, 1);
        const day = Math.floor((Date.UTC(+parts.year, +parts.month - 1, +parts.day) - start) / 864e5) + 1;

        return { hours: +parts.hour + +parts.minute / 60, offset, day };
    }

    function paint(hours, offset, day) {
        const decl = 23.44 * Math.sin(2 * Math.PI * (284 + day) / 365) * rad;
        const lat = LAT * rad;
        const solarShift = -offset + LON / 15;
        const hourAngle = 15 * (hours + solarShift - 12) * rad;
        const elevation = Math.asin(Math.sin(lat) * Math.sin(decl) + Math.cos(lat) * Math.cos(decl) * Math.cos(hourAngle)) / rad;

        const cosH0 = (Math.sin(-0.833 * rad) - Math.sin(lat) * Math.sin(decl)) / (Math.cos(lat) * Math.cos(decl));
        const halfDay = Math.acos(Math.min(1, Math.max(-1, cosH0))) / rad / 15;
        const sunrise = 12 - halfDay - solarShift, sunset = 12 + halfDay - solarShift;

        let i = stops.findIndex(s => elevation < s[0]);
        if (i === -1) i = stops.length - 1;
        const [e0, t0, b0] = stops[Math.max(0, i - 1)], [e1, t1, b1] = stops[i];
        const t = e1 === e0 ? 0 : Math.min(1, Math.max(0, (elevation - e0) / (e1 - e0)));

        // Cloud greys the sky; rain and snow grey it further.
        const night = Math.min(1, Math.max(0, (-2 - elevation) / 10));
        const grey = Math.min(0.85, Math.pow(weather.clouds, 1.5) * 0.7 + (weather.precipitation ? 0.15 : 0) + weather.fog * 0.3);
        const top = mix(mix(t0, t1, t), mix('#7a8796', '#141a24', night), grey);
        const bottom = mix(mix(b0, b1, t), mix('#b8c1cb', '#252c38', night), grey);

        const style = sky.style;
        style.setProperty('--sky-top', top);
        style.setProperty('--sky-bottom', bottom);
        style.setProperty('--stars', Math.min(1, Math.max(0, (-6 - elevation) / 8)).toFixed(2));
        style.setProperty('--name-edge', elevation < -6 ? 'rgb(214 226 255 / 0.55)' : 'transparent');

        // The sun travels left to right between sunrise and sunset.
        const progress = (hours - sunrise) / (sunset - sunrise);
        style.setProperty('--sun-x', (8 + 84 * Math.min(1, Math.max(0, progress))).toFixed(1) + '%');
        style.setProperty('--sun-y', Math.max(-10, elevation * 1.6).toFixed(1) + '%');
        style.setProperty('--sun-visible', elevation > -3 ? (1 - Math.max(0, weather.clouds - 0.5) * 1.8).toFixed(2) : 0);
        style.setProperty('--sun-color', elevation < 6 ? '#ffc46b' : '#fff3c4');

        return { sunrise, sunset, up: elevation > -0.833 };
    }

    const clock = h => {
        const hr = Math.floor(h) % 24, min = Math.round((h % 1) * 60) % 60;
        return `${hr % 12 || 12}:${String(min).padStart(2, '0')} ${hr < 12 ? 'am' : 'pm'}`;
    };

    // ?time=19:05 (or ?time=7) previews the sky at that hour, and
    // ?date=2026-06-21 on that day, for checking dusk, night or midsummer
    // without waiting for them. Either works alone; the other stays as now.
    const query = new URLSearchParams(location.search);
    const previewTime = (() => {
        const match = /^(\d{1,2})(?::(\d{2}))?$/.exec(query.get('time') || '');
        return match && +match[1] < 24 && +(match[2] || 0) < 60 ? +match[1] + +(match[2] || 0) / 60 : null;
    })();
    const previewDate = (() => {
        const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(query.get('date') || '');
        // Noon in Winnipeg on that day, which is safely clear of a DST change.
        const at = match && new Date(Date.UTC(+match[1], +match[2] - 1, +match[3], 18));
        return at && ! isNaN(at) ? at : null;
    })();
    const previewing = previewTime !== null || previewDate !== null;

    // The weather: what the sky draws, from /weather.json or a ?weather= preview.
    const calm = { clouds: 0, precipitation: null, intensity: 0, fog: 0, wind_kph: null, wind_from_deg: null, temperature_c: null, condition: null };
    const presets = {
        clear: { clouds: 0.05, condition: 'Clear', temperature_c: 14 },
        cloudy: { clouds: 0.55, condition: 'PartlyCloudy', temperature_c: 12, wind_kph: 15, wind_from_deg: 270 },
        overcast: { clouds: 1, condition: 'Cloudy', temperature_c: 9, wind_kph: 20, wind_from_deg: 300 },
        rain: { clouds: 0.95, precipitation: 'rain', intensity: 0.5, condition: 'Rain', temperature_c: 8, wind_kph: 20, wind_from_deg: 250 },
        storm: { clouds: 1, precipitation: 'rain', intensity: 1, condition: 'Thunderstorms', temperature_c: 18, wind_kph: 50, wind_from_deg: 240 },
        snow: { clouds: 0.9, precipitation: 'snow', intensity: 0.5, condition: 'Snow', temperature_c: -8, wind_kph: 15, wind_from_deg: 320 },
        fog: { clouds: 0.6, visibility_km: 0.6, condition: 'Foggy', temperature_c: 6 },
        windy: { clouds: 0.35, condition: 'Windy', temperature_c: 11, wind_kph: 55, wind_from_deg: 280 },
        aurora: { clouds: 0.05, condition: 'Clear', temperature_c: -14, aurora: { chance_in_view: 0.6, kp: 6, storm_scale: 'G2' } },
    };
    const previewWeather = presets[query.get('weather')] ?? null;
    let weather = { ...calm };

    function setWeather(data) {
        weather = { ...calm, ...(data || {}) };
        weather.fog = weather.visibility_km == null ? 0 : Math.min(1, Math.max(0, (5 - weather.visibility_km) / 4.5));
        const kph = weather.wind_kph || 0;
        // Wind from the west blows the clouds east, and the other way round.
        const east = weather.wind_from_deg == null || (weather.wind_from_deg > 180 && weather.wind_from_deg < 360);

        const style = sky.style;
        style.setProperty('--clouds', weather.clouds.toFixed(2));
        style.setProperty('--fog', weather.fog.toFixed(2));
        style.setProperty('--rain', weather.precipitation === 'rain' ? (0.35 + weather.intensity * 0.65).toFixed(2) : 0);
        style.setProperty('--snow', weather.precipitation === 'snow' ? (0.4 + weather.intensity * 0.6).toFixed(2) : 0);
        style.setProperty('--drift', Math.max(70, 260 - kph * 4) + 's');
        style.setProperty('--drift-dir', east ? 'normal' : 'reverse');
        style.setProperty('--slant', ((east ? 1 : -1) * Math.min(25, kph * 0.4)).toFixed(0) + 'deg');

        // Nobody flies in rain or snow, in fog, in a gale or with thunder
        // about. Overcast alone is fine: small planes fly under a grey sky.
        const thunder = /thunder/i.test(weather.condition || '');
        sky.toggleAttribute('data-grounded', !! weather.precipitation || weather.fog > 0.4 || kph > 45 || thunder);
        sky.toggleAttribute('data-storm', thunder);

        drawClouds();
        update();
    }

    // Seven clouds, always there; the cloud cover decides how many show.
    const cloudShape = '<svg viewBox="0 0 120 44" class="w-full h-auto"><ellipse cx="34" cy="28" rx="26" ry="14"/><ellipse cx="60" cy="20" rx="27" ry="18"/><ellipse cx="88" cy="28" rx="24" ry="13"/><ellipse cx="60" cy="33" rx="50" ry="10"/></svg>';
    const cloudSlots = [[6, 8, 0.0], [18, 22, 0.55], [10, 46, 0.3], [26, 70, 0.8], [4, 88, 0.15], [30, 30, 0.65], [14, 60, 0.42]];

    function drawClouds() {
        const layer = sky.querySelector('[data-sky-clouds]');
        if (! layer.childElementCount) {
            cloudSlots.forEach(([top, left, phase], i) => {
                const cloud = document.createElement('div');
                cloud.className = 'sky-cloud';
                cloud.style.cssText = `top:${top}%;left:${left}%;width:${[9, 12, 7, 10, 13, 8, 11][i]}em;animation-delay:calc(var(--drift) * -${phase})`;
                cloud.innerHTML = cloudShape;
                layer.append(cloud);
            });
        }
        const showing = weather.clouds < 0.08 ? 0 : Math.max(1, Math.round(weather.clouds * cloudSlots.length));
        [...layer.children].forEach((cloud, i) => cloud.hidden = i >= showing);
    }

    // A flash of lightning now and then, when there's thunder about.
    if (! matchMedia('(prefers-reduced-motion: reduce)').matches) {
        (function lightning() {
            setTimeout(() => {
                if (sky.hasAttribute('data-storm')) {
                    sky.querySelector('.sky-flash').animate([{ opacity: 0 }, { opacity: 0.7 }, { opacity: 0.1 }, { opacity: 0.5 }, { opacity: 0 }], 600);
                }
                lightning();
            }, 5000 + Math.random() * 9000);
        })();
    }

    // Northern lights, low over the town: NOAA's chance of one in view to the
    // north, or a strong Kp, on a dark night clear enough to see it.
    function aurora() {
        const a = weather.aurora;
        const dark = parseFloat(sky.style.getPropertyValue('--stars')) > 0.5;
        if (! a || ! dark || weather.clouds > 0.6 || weather.precipitation) return 0;

        const strength = Math.max((a.chance_in_view ?? 0) * 1.6, ((a.kp ?? 0) - 4) / 3);
        return (a.chance_in_view ?? 0) >= 0.15 || (a.kp ?? 0) >= 5 ? Math.min(1, Math.max(0.35, strength)) : 0;
    }

    const describe = w => w.condition ? w.condition.replace(/([a-z])([A-Z])/g, '$1 $2').toLowerCase() : null;

    // Today in Steinbach, on the home page: the same day the sky is drawing.
    const compass = ['north', 'north-northeast', 'northeast', 'east-northeast', 'east', 'east-southeast', 'southeast', 'south-southeast',
        'south', 'south-southwest', 'southwest', 'west-southwest', 'west', 'west-northwest', 'northwest', 'north-northwest'];

    function almanac(sun, hours, when, lights) {
        const set = (key, text) => {
            const cell = document.querySelector(`[data-almanac=${key}]`);
            if (cell) cell.textContent = text;
        };
        const length = sun.sunset - sun.sunrise;
        const night = parseFloat(sky.style.getPropertyValue('--stars')) > 0.5;
        const a = weather.aurora;

        set('date', when);
        set('sunrise', clock(sun.sunrise));
        set('sunset', clock(sun.sunset));
        set('daylight', `${Math.floor(length)} h ${Math.round((length % 1) * 60)} min`);

        const conditions = [weather.temperature_c == null ? null : `${Math.round(weather.temperature_c)}°`, describe(weather)].filter(Boolean).join(' and ');
        set('weather', conditions || '—');
        set('wind', weather.wind_kph == null ? '—'
            : `${Math.round(weather.wind_kph)} km/h` + (weather.wind_from_deg == null ? '' : ` from the ${compass[Math.round(weather.wind_from_deg / 22.5) % 16]}`));
        set('aurora', lights ? 'Look north tonight'
            : ! a ? '—'
            : ! night ? 'Not while the sun’s up'
            : weather.clouds > 0.6 ? 'Hidden by cloud'
            : (a.chance_in_view ?? 0) >= 0.05 ? 'Possible, low in the north'
            : 'Not tonight');
        set('planes', sky.hasAttribute('data-grounded') ? 'Grounded by the weather'
            : night ? 'Flying with their lights on'
            : 'Three up, one looping');
    }

    function update() {
        const now = winnipegNow();
        const day = previewDate ? winnipegNow(previewDate) : now;
        const pinned = sky.dataset.skyTime;
        const hours = pinned ? parseFloat(pinned) : previewTime ?? now.hours;
        const sun = paint(hours, day.offset, day.day);
        const lights = aurora();
        sky.style.setProperty('--aurora', lights.toFixed(2));

        // NOAA's courtesy credit in the footer, while there's an aurora to credit.
        const noaa = document.querySelector('[data-aurora-credit]');
        if (noaa) noaa.hidden = ! lights || !! previewWeather;

        const today = (previewDate ?? new Date()).toLocaleDateString('en-CA', { weekday: 'long', month: 'long', day: 'numeric', timeZone: previewDate ? 'UTC' : 'America/Winnipeg' });
        almanac(sun, hours, (previewing || previewWeather ? 'Previewing ' : '') + today + ', ' + clock(hours), lights);

        const line = document.querySelector('[data-sky-clock]');
        if (line && ! pinned) {
            const when = (previewDate ? previewDate.toLocaleDateString('en-CA', { month: 'long', day: 'numeric', timeZone: 'UTC' }) + ', ' : '') + clock(hours);
            const conditions = [weather.temperature_c == null ? null : `${Math.round(weather.temperature_c)}°`, describe(weather)].filter(Boolean).join(' and ');
            line.textContent = (previewing || previewWeather ? `Previewing ${when}` : `It's ${when}`)
                + ' in Steinbach, Manitoba. '
                + (conditions ? conditions.charAt(0).toUpperCase() + conditions.slice(1) + '. ' : '')
                + (lights ? 'Northern lights to the north. ' : '')
                + (sun.up ? `Sunset at ${clock(sun.sunset)}.` : `Sunrise at ${clock(sun.sunrise < hours ? sun.sunrise + 24 : sun.sunrise)}.`);
        }
    }

    // Paint now, before the page below is parsed; fill in the clock once it is.
    update();
    document.addEventListener('DOMContentLoaded', () => {
        update();

        if (previewWeather) return setWeather(previewWeather);

        // The real weather, cached for the hour at the edge; checked again
        // every quarter hour for a page left open. The date and hour (UTC)
        // make each hour its own URL, so a browser holding on to an older
        // copy can't keep showing it.
        const fetchWeather = () => fetch('/weather.json?at=' + new Date().toISOString().slice(0, 13))
            .then(response => response.ok ? response.json() : null)
            .then(data => data?.weather && setWeather(data.weather))
            .catch(() => {});
        fetchWeather();
        setInterval(fetchWeather, 15 * 60000);
    });
    setInterval(update, 60000);
})();
</script>
