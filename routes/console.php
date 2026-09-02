<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Replaces Statamic's per-minute HandleEntrySchedule job, which is disabled via
// STATAMIC_HANDLE_SCHEDULED_ENTRIES=false. See the command for why that job
// cannot just be run less often.
Schedule::command('entries:handle-hourly-schedule')->hourly();
