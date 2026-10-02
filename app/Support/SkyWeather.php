<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * The weather over Steinbach for the sky in the header, from aroundfor.com.
 *
 * Asked for at most once an hour, and only when someone wants it: nothing is
 * scheduled, so the container is never woken just to look at the sky. The
 * answer is trimmed to what the sky draws, so nothing else from aroundfor
 * reaches the browser.
 */
class SkyWeather
{
    private const CACHE_KEY = 'sky-weather';

    /**
     * @return array{as_of: string|null, condition: string|null, clouds: float, precipitation: string|null, intensity: float, wind_kph: float|null, wind_from_deg: float|null, visibility_km: float|null, temperature_c: float|null, daylight: bool|null, summary: string|null, aurora: array{chance_in_view: float|null, chance_overhead: float|null, kp: float|null, storm_scale: string|null}|null, attribution: array{mark_url: string|null, mark_dark_url: string|null, legal_url: string}}|null
     */
    public function current(): ?array
    {
        if (! config('services.aroundfor.token')) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY);

        if ($cached !== null) {
            return $cached ?: null;
        }

        $weather = $this->fetch();

        // A miss is remembered too, briefly, so an outage at aroundfor isn't
        // asked about on every page view.
        Cache::put(self::CACHE_KEY, $weather ?? false, $weather ? $this->untilNextHour() : 300);

        return $weather;
    }

    /**
     * Seconds left in this hour: the answer is for the coming hour, and the
     * next one is asked for once it starts.
     */
    public function untilNextHour(): int
    {
        return max(300, now()->addHour()->startOfHour()->diffInSeconds(now(), true));
    }

    private function fetch(): ?array
    {
        try {
            $response = Http::withToken(config('services.aroundfor.token'))
                ->acceptJson()
                ->timeout(4)
                ->get(rtrim(config('services.aroundfor.url'), '/').'/api/public/weather', [
                    'lat' => config('services.aroundfor.latitude'),
                    'lon' => config('services.aroundfor.longitude'),
                    'timezone' => 'America/Winnipeg',
                ]);
        } catch (Throwable) {
            return null;
        }

        $current = $response->successful() ? $response->json('current') : null;

        if (! is_array($current)) {
            return null;
        }

        $number = fn (string $key): ?float => is_numeric($current[$key] ?? null) ? (float) $current[$key] : null;
        $type = $current['precipitation_type'] ?? null;
        $intensity = $number('precipitation_intensity') ?? 0.0;

        return [
            'as_of' => $response->json('as_of'),
            'condition' => $current['condition_code'] ?? null,
            'clouds' => min(1, max(0, $number('cloud_cover') ?? 0)),
            // Rain, sleet, hail, mixed and Apple's plain "precipitation" all fall
            // as rain here; snow falls as snow.
            'precipitation' => $intensity > 0.05 && $type && $type !== 'clear' ? ($type === 'snow' ? 'snow' : 'rain') : null,
            // mm/h on a 0–1 scale: 4 mm/h and up is a downpour.
            'intensity' => round(min(1, $intensity / 4), 2),
            'wind_kph' => $number('wind_speed_kph'),
            'wind_from_deg' => $number('wind_direction_deg'),
            'visibility_km' => ($visibility = $number('visibility_m')) === null ? null : round($visibility / 1000, 1),
            'temperature_c' => ($temperature = $number('temperature_c')) === null ? null : round($temperature),
            'daylight' => isset($current['is_daylight']) ? (bool) $current['is_daylight'] : null,
            'summary' => $response->json('next_hour.summary'),
            // From NOAA's Space Weather Prediction Center, by way of aroundfor:
            // the chance of an aurora low on the northern horizon, and Kp.
            'aurora' => $this->aurora($response->json('aurora')),
            // Apple's terms: its mark and legal link wherever the data shows.
            'attribution' => [
                'mark_url' => $response->json('attribution.mark_url'),
                'mark_dark_url' => $response->json('attribution.mark_dark_url'),
                'legal_url' => $response->json('attribution.legal_url') ?? 'https://developer.apple.com/weatherkit/data-source-attribution/',
            ],
        ];
    }

    /**
     * @return array{chance_in_view: float|null, chance_overhead: float|null, kp: float|null, storm_scale: string|null}|null
     */
    private function aurora(mixed $aurora): ?array
    {
        if (! is_array($aurora)) {
            return null;
        }

        $number = fn (string $key): ?float => is_numeric($aurora[$key] ?? null) ? round((float) $aurora[$key], 2) : null;

        return [
            'chance_in_view' => $number('chance_in_view'),
            'chance_overhead' => $number('chance_overhead'),
            'kp' => $number('kp'),
            'storm_scale' => is_string($aurora['storm_scale'] ?? null) ? $aurora['storm_scale'] : null,
        ];
    }
}
