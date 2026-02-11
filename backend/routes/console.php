<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule task due notifications to run daily at 9:00 AM
Schedule::command('tasks:check-due')
    ->dailyAt('09:00')
    ->timezone('UTC')
    ->description('Check for tasks due soon and send email notifications');
