<?php

namespace App\Http\Controllers;

use App\Support\SkyWeather;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    public function __invoke(SkyWeather $weather): JsonResponse
    {
        $current = $weather->current();

        return response()->json(['weather' => $current], 200, [
            // The edge keeps it for the rest of the hour, so the container is
            // asked at most once an hour; browsers only for ten minutes, so a
            // page left open picks up the next hour soon after it starts.
            // With no answer, the edge checks back in five minutes.
            'Cache-Control' => 'public, max-age=600, s-maxage='.($current ? $weather->untilNextHour() : 300),
        ]);
    }
}
