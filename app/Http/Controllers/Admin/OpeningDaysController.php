<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\OpeningDay;
use App\Http\Controllers\Controller;
use App\Http\Requests\OpeningDayRequest;

class OpeningDaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $openingDurations = OpeningDay::all();

        return view('admin.opening-days.edit', compact('openingDurations'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(OpeningDayRequest $request)
    {
        $validated = $request->validated();

        foreach ($validated['duration_ids'] as $index => $id) {
            $day = OpeningDay::find($id);
            if ($day) {
                $startTime = Carbon::parse($validated['start_time'][$index]);
                $endTime = Carbon::parse($validated['end_time'][$index]);
                $totalMinutes = $endTime->diffInMinutes($startTime);

                $day->update([
                    'start_time' => $validated['start_time'][$index],
                    'end_time' => $validated['end_time'][$index],
                    'status' => in_array($id, $validated['day_of_week'] ?? []),
                    'total_mins' => $totalMinutes,
                ]);

            }
        }
        $inActiveOpeningDays = OpeningDay::whereNotIn('id',$request->day_of_week )->update(['status' => false]);
        return redirect()->route('admin.opening-days.index')
                         ->with('success', 'Work hours updated successfully.');
    }
    

    
}
