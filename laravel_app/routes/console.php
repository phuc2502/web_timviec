<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Task 1: Publish scheduled jobs (every minute)
Schedule::command('listings:publish-scheduled')->everyMinute();

// Task 2: Expire listings (daily at 00:00)
Schedule::command('listings:expire')->daily();

// Task 3: Archive rejected listings (daily at 01:00)
Schedule::command('listings:archive-rejected')->dailyAt('01:00');

// Task 4: Send expiry reminders (daily at 09:00)
Schedule::command('listings:send-expiry-reminders')->dailyAt('09:00');

