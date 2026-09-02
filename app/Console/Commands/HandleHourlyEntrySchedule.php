<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Events\EntryScheduleReached;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

/**
 * Hourly replacement for Statamic's own scheduled-entry handling.
 *
 * Statamic registers Statamic\Jobs\HandleEntrySchedule to run every minute, and
 * that job cannot simply be slowed down: it builds its query from a single
 * minute (Statamic\Entries\MinuteEntries matches H:i:00 to H:i:59), so running
 * it hourly would silently skip 59 of every 60 minutes' worth of entries.
 *
 * This covers a full hour instead. Set STATAMIC_HANDLE_SCHEDULED_ENTRIES=false
 * so Statamic does not also register its per-minute job.
 */
class HandleHourlyEntrySchedule extends Command
{
    protected $signature = 'entries:handle-hourly-schedule';

    protected $description = "Dispatch EntryScheduleReached for entries whose scheduled date fell in the previous hour";

    public function handle(): int
    {
        // A whole clock hour, so consecutive runs neither overlap nor leave gaps.
        // At 10:00 this covers 09:00:00 through 09:59:59.
        $end = now()->startOfHour();
        $start = $end->copy()->subHour();

        $entries = Entry::query()
            ->whereIn('collection', Collection::all()->filter->dated()->map->handle()->all())
            ->where('date', '>=', $start)
            ->where('date', '<', $end)
            ->get();

        $entries->each(fn ($entry) => EntryScheduleReached::dispatch($entry));

        $this->info(sprintf(
            'Dispatched %d entr%s scheduled between %s and %s.',
            $entries->count(),
            $entries->count() === 1 ? 'y' : 'ies',
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ));

        return self::SUCCESS;
    }
}
