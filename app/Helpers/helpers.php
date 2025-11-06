<?php

use Illuminate\Support\Carbon;


if (! function_exists('currencyFormatter')) {
    function currencyFormatter($number)
    {
        return '£' . $number;
    }
}


 function ConvertStringToDateTime($date, $time)
 {
    $datetime = Carbon::parse($date)
        ->setTime(substr($time, 0, 2), substr($time, 2));

    return $datetime->toDateTimeString(); // Output: 2025-05-29 14:00:00
 }

 function getAllBookingTimeJson($bookingDate, array $times)
 {
    foreach($times as $time){
            $startDate = ConvertStringToDateTime($bookingDate, substr($time, 0, 4));
            $endDate = ConvertStringToDateTime($bookingDate, substr($time, 5, 4));
            $dates[] = ['startDate' => $startDate , 'endDate' => $endDate];
    }
    return json_encode($dates);
 }

/**
 * Get a setting value by key
 */
if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(\App\Services\SettingsService::class)->get($key, $default);
    }
}

/**
 * Set a setting value
 */
if (!function_exists('setSetting')) {
    function setSetting(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        app(\App\Services\SettingsService::class)->set($key, $value, $type, $group);
    }
}

/**
 * Get all settings for a specific group
 */
if (!function_exists('settingsGroup')) {
    function settingsGroup(string $group): array
    {
        return app(\App\Services\SettingsService::class)->getGroup($group);
    }
}

/**
 * Get all public settings (safe for frontend)
 */
if (!function_exists('publicSettings')) {
    function publicSettings(): array
    {
        return app(\App\Services\SettingsService::class)->getPublic();
    }
}
