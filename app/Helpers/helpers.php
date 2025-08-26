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
