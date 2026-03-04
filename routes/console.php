<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Scheduled tasks for production.
 */
Schedule::command('app:cleanup')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->description('Clean up expired OTP requests and mark expired invoices');

Schedule::command('queue:prune-batches --hours=48')
    ->daily()
    ->at('03:00')
    ->description('Prune old batch records');

Schedule::command('activitylog:clean --days=90')
    ->weekly()
    ->sundays()
    ->at('04:00')
    ->description('Clean old activity log records');
