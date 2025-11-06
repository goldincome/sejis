<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule low stock alerts
Schedule::command('inventory:send-low-stock-alerts --frequency=daily')
    ->daily()
    ->at('09:00')
    ->name('daily-low-stock-alerts')
    ->description('Send daily low stock alerts to administrators')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Daily low stock alerts failed to send');
    });

// Schedule weekly low stock reports
Schedule::command('inventory:send-low-stock-alerts --frequency=weekly')
    ->weekly()
    ->mondays()
    ->at('08:00')
    ->name('weekly-low-stock-report')
    ->description('Send weekly comprehensive low stock report')
    ->withoutOverlapping();

// Schedule urgent/critical alerts more frequently
Schedule::command('inventory:send-low-stock-alerts --urgency=critical --frequency=hourly')
    ->hourly()
    ->between('8:00', '18:00') // Business hours only
    ->name('critical-stock-alerts')
    ->description('Send critical stock alerts during business hours')
    ->withoutOverlapping();
