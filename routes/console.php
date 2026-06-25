<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// MDM PostgreSQL sync — runs every 5 minutes, skips if previous run is still going.
// Interval can be changed: everyMinute, everyFiveMinutes, everyTenMinutes, everyThirtyMinutes, hourly.
Schedule::command('mdm:sync --user=1')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)      // lock expires after 10 min in case process dies
    ->runInBackground()           // don't block the scheduler process
    ->appendOutputTo(storage_path('logs/mdm-sync.log'));

