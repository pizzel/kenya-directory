<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// 1. Generate Sitemap Daily
// Runs at 2:00 AM server time.
Schedule::command('sitemap:generate')
        ->daily()
        ->at('02:00');

// 2. Weekly Business Enrichment & Review Refresh
// Runs every Sunday at 3:00 AM.
// - '--force': Check ALL businesses to get fresh reviews and descriptions.
// - 'runInBackground': Critical because this process takes ~20 mins.
// - 'withoutOverlapping': Safety mechanism to ensure only one instance runs at a time.
Schedule::command('businesses:enrich --force')
        ->weeklyOn(0, '03:00') 
        ->runInBackground()
        ->withoutOverlapping();

// ROTATE HERO SLIDERS (Hourly)
// Picks 10 new random businesses every hour to keep the homepage fresh.
Schedule::command('hero:rotate --count=10')
        ->hourly()
        ->runInBackground()
        ->withoutOverlapping();

Schedule::command('newsletter:send')->weeklyOn(1, '08:00');