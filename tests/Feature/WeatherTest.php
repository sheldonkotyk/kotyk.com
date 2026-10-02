<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.aroundfor.url' => 'https://aroundfor.test',
            'services.aroundfor.token' => 'secret-token',
        ]);
    }

    public function test_weather_is_trimmed_to_what_the_sky_draws(): void
    {
        Http::fake(['aroundfor.test/*' => Http::response([
            'as_of' => '2026-10-02T15:00:00Z',
            'current' => [
                'condition_code' => 'Drizzle',
                'cloud_cover' => 0.92,
                'precipitation_intensity' => 1.0,
                'precipitation_type' => 'rain',
                'temperature_c' => 9.6,
                'wind_speed_kph' => 22,
                'wind_direction_deg' => 290,
                'visibility_m' => 8000,
                'is_daylight' => true,
                'humidity' => 0.9,
            ],
            'next_hour' => ['summary' => 'Drizzle for the hour', 'minutes' => []],
            'aurora' => [
                'as_of' => '2026-10-02T14:50:00Z',
                'forecast_for' => '2026-10-02T16:20:00Z',
                'chance_overhead' => 0.05,
                'chance_in_view' => 0.3,
                'kp' => 3.6667,
                'kp_as_of' => '2026-10-02T15:00:00Z',
                'kp_max_24h' => 5.33,
                'storm_scale' => 'G2',
            ],
            'attribution' => [
                'mark_url' => 'https://weatherkit.apple.com/mark-black.png',
                'mark_dark_url' => 'https://weatherkit.apple.com/mark-white.png',
                'legal_url' => 'https://developer.apple.com/weatherkit/data-source-attribution/',
            ],
        ])]);

        $this->getJson('/weather.json')
            ->assertOk()
            ->assertExactJson(['weather' => [
                'as_of' => '2026-10-02T15:00:00Z',
                'condition' => 'Drizzle',
                'clouds' => 0.92,
                'precipitation' => 'rain',
                'intensity' => 0.25,
                'wind_kph' => 22,
                'wind_from_deg' => 290,
                'visibility_km' => 8,
                'temperature_c' => 10,
                'daylight' => true,
                'summary' => 'Drizzle for the hour',
                'aurora' => [
                    'chance_in_view' => 0.3,
                    'chance_overhead' => 0.05,
                    'kp' => 3.67,
                    'storm_scale' => 'G2',
                ],
                'attribution' => [
                    'mark_url' => 'https://weatherkit.apple.com/mark-black.png',
                    'mark_dark_url' => 'https://weatherkit.apple.com/mark-white.png',
                    'legal_url' => 'https://developer.apple.com/weatherkit/data-source-attribution/',
                ],
            ]]);

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer secret-token')
            && str_starts_with($request->url(), 'https://aroundfor.test/api/public/weather?lat=49.53&lon=-96.68&timezone=America%2FWinnipeg'));
    }

    public function test_aroundfor_is_asked_once_an_hour_and_the_edge_may_cache_the_answer(): void
    {
        Http::fake(['aroundfor.test/*' => Http::response(['current' => ['cloud_cover' => 0.1]])]);

        $response = $this->get('/weather.json');
        $this->get('/weather.json');

        Http::assertSentCount(1);
        $response->assertHeaderMissing('Set-Cookie');
        $this->assertStringContainsString('s-maxage=', $response->headers->get('Cache-Control'));
    }

    public function test_apples_plain_precipitation_falls_as_rain(): void
    {
        Http::fake(['aroundfor.test/*' => Http::response(['current' => [
            'precipitation_type' => 'precipitation',
            'precipitation_intensity' => 2.0,
        ]])]);

        $this->getJson('/weather.json')
            ->assertJsonPath('weather.precipitation', 'rain')
            ->assertJsonPath('weather.intensity', 0.5);
    }

    public function test_when_apple_has_no_answer_there_is_no_weather(): void
    {
        Http::fake(['aroundfor.test/*' => Http::response(['as_of' => null, 'current' => null, 'next_hour' => null])]);

        $this->getJson('/weather.json')->assertExactJson(['weather' => null]);
    }

    public function test_an_outage_means_no_weather_and_a_short_cache(): void
    {
        Http::fake(['aroundfor.test/*' => Http::response('', 503)]);

        $this->getJson('/weather.json')
            ->assertOk()
            ->assertExactJson(['weather' => null])
            ->assertHeader('Cache-Control', 'max-age=600, public, s-maxage=300');
    }

    public function test_without_a_token_the_sky_has_no_weather_and_aroundfor_is_not_asked(): void
    {
        config(['services.aroundfor.token' => null]);
        Http::fake();

        $this->getJson('/weather.json')->assertExactJson(['weather' => null]);

        Http::assertNothingSent();
    }
}
