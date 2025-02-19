<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\GenerateMonthlyReport;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('schedule:run', function () {
    // Schedule your monthly report command
    Artisan::call(GenerateMonthlyReport::class);
})->monthlyOn(1, '08:00');  // Example: Run on the 1st of each month at 8 AM