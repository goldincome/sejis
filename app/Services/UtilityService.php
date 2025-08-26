<?php 
namespace App\Services;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilityService{
    
    public function getTimeSlots(Request $request)
    {
        $selectedDate = Carbon::parse($request->booking_date);
        $dayName = $selectedDate->format('l'); // e.g., Monday

        // Fetch store opening hours for this day
        $storeHours = DB::table('opening_days')
            ->where('day_of_week', $dayName)
            ->where('status', true)
            ->first();

        if (!$storeHours) {
            return response()->json(['slots' => []]);
        }

        // Construct opening and closing times for the selected date
        $openTime = Carbon::parse($selectedDate->toDateString() . ' ' . $storeHours->start_time);
        $closeTime = Carbon::parse($selectedDate->toDateString() . ' ' . $storeHours->end_time);

        // Fetch conflicting public holiday time ranges
        $conflicts = DB::table('holiday_schedules')
            ->whereDate('date_from', '<=', $selectedDate)
            ->whereDate('date_to', '>=', $selectedDate)
            ->get();

        $conflictingRanges = collect($conflicts)->map(function ($item) use ($selectedDate) {
            return [
                'start' => Carbon::parse($selectedDate->toDateString() . ' ' . Carbon::parse($item->date_from)->format('H:i:s')),
                'end' => Carbon::parse($selectedDate->toDateString() . ' ' . Carbon::parse($item->date_to)->format('H:i:s')),
            ];
        });

        
        /*// Fetch existing bookings for the selected date
        $bookings = DB::table('bookings')
            ->whereDate('startDateTime', $selectedDate)
            ->orWhereDate('endDateTime', $selectedDate)
            ->get();

        $bookedRanges = collect($bookings)->map(function ($booking) {
            return [
                'start' => Carbon::parse($booking->startDateTime),
                'end' => Carbon::parse($booking->endDateTime),
            ];
        });
        */

        // Generate 1-hour slots
        $slots = [];
        $current = $openTime->copy();

        while ($current->lt($closeTime)) {
            $end = $current->copy()->addHour();
            if ($end->gt($closeTime)) break;

            // Check overlap with public holidays
            $overlapsHoliday = $conflictingRanges->contains(function ($range) use ($current, $end) {
                return $current->lt($range['end']) && $end->gt($range['start']);
            });
            $overlapsBooking = null;
            /*// Check overlap with existing bookings
            $overlapsBooking = $bookedRanges->contains(function ($booking) use ($current, $end) {
                return $current->lt($booking['end']) && $end->gt($booking['start']);
            });
            */

            if (!$overlapsHoliday && !$overlapsBooking) {
                $slots[] = $current->format('g:i A') . ' - ' . $end->format('g:i A');
            }

            $current->addHour();
        }

        return response()->json(['slots' => $slots]);
    }
}