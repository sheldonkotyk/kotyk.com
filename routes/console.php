<?php

use Abigah\BotCopTrafficClient\Facades\MonitoringClient;
use Illuminate\Support\Facades\Schedule;

// Replaces Statamic's per-minute HandleEntrySchedule job, which is disabled via
// STATAMIC_HANDLE_SCHEDULED_ENTRIES=false. See the command for why that job
// cannot just be run less often.
//
// The heartbeat rides on this command rather than on a job added for the
// purpose: it already runs hourly, so it costs no extra wake on a scale-to-zero
// container, and its silence is a real symptom - scheduled entries stop
// publishing. A synthetic heartbeat job would only prove that the thing added
// for monitoring still runs.
//
// onSuccess/onFailure rather than the package's job listener, because a
// scheduled command is not dispatched through the queue and JobProcessed never
// fires for it. Both hooks are no-ops until the token is set.
Schedule::command('entries:handle-hourly-schedule')
    ->hourly()
    ->onSuccess(fn () => MonitoringClient::ping(
        config('monitoring-client.heartbeats.scheduled.entries:handle-hourly-schedule'),
    ))
    ->onFailure(fn () => MonitoringClient::fail(
        config('monitoring-client.heartbeats.scheduled.entries:handle-hourly-schedule'),
        'entries:handle-hourly-schedule exited non-zero',
    ));
