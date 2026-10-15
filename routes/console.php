<?php

use App\Services\AlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('alerts:scan', function (AlertService $alerts) {
    foreach ($alerts->scanAll() as $key => $count) {
        $this->line("{$key}: {$count}");
    }

    $this->info('Alert scan selesai.');
})->purpose('Scan ERP conditions and dispatch notifications');

Schedule::command('alerts:scan')->dailyAt('07:00');
