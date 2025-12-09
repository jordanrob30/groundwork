<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| These tasks are scheduled to run at specified intervals.
| Per constitution requirements, all async operations use Redis queues.
|
*/

// Poll mailboxes for replies (every minute in local, every 5 minutes in production)
if (app()->environment('local', 'testing')) {
    Schedule::command('mailbox:poll')->everyMinute();
} else {
    Schedule::command('mailbox:poll')->everyFiveMinutes();
}

// Update warm-up limits daily at midnight
Schedule::command('mailbox:warmup')->dailyAt('00:05');

// Schedule day's emails (every minute in local for testing, daily at 8am in production)
if (app()->environment('local', 'testing')) {
    Schedule::command('emails:schedule')->everyMinute();
} else {
    Schedule::command('emails:schedule')->dailyAt('08:00');
}

// Generate insights for active campaigns hourly
Schedule::command('insights:generate')->hourly();
