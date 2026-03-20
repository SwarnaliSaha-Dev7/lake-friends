<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every April 1st at midnight — processes the closing FY (Apr 1 - Mar 31)
Schedule::command('fines:process-year-end')
    ->yearlyOn(4, 1, '00:05')
    ->withoutOverlapping()
    ->runInBackground();
