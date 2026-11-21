<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

    use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('waiting-list:expire-offers')
    ->everyMinute()
    ->withoutOverlapping();




Schedule::command('subscriptions:expiry-reminders')
    ->everyMinute()
    ->withoutOverlapping();
Schedule::command('subscriptions:activate-due-renewals')->everyMinute()->withoutOverlapping();


Schedule::command('reviews:refresh-summaries')->dailyAt('03:00');

