<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HolidaySchedule;
use App\Http\Controllers\Controller;
use App\Services\HolidayScheduleService;
use App\Http\Requests\HolidayScheduleRequest;

class HolidayScheduleController extends Controller
{
    protected $holidayScheduleService;

    public function __construct(HolidayScheduleService $holidayScheduleService)
    {
        $this->holidayScheduleService = $holidayScheduleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidaySchedules = $this->holidayScheduleService->getAllHolidaySchedules();
        return view('admin.holiday-schedules.index', compact('holidaySchedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.holiday-schedules.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HolidayScheduleRequest $request)
    {
        if($this->holidayScheduleService->checkExistingOffDateEntry($request->date_to))
        {
            return redirect()->back()->with('error', "This date : ".Carbon::parse($request->date_to)->format('d F, Y'). ' is already existing');
        }
        $this->holidayScheduleService->createHolidaySchedule($request->validated());

        return redirect()->route('admin.off-dates.index')
                         ->with('success', 'Public Holiday created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HolidaySchedule $off_date)
    {
        return view('admin.holiday-schedules.show', compact('off_date'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HolidaySchedule $off_date)
    {
        return view('admin.holiday-schedules.edit', compact('off_date'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HolidayScheduleRequest $request, HolidaySchedule $off_date)
    {
        $this->holidayScheduleService->updateHolidaySchedule($off_date, $request->validated());

        return redirect()->route('admin.off-dates.index')
                         ->with('success', 'Public Holiday updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HolidaySchedule $off_date)
    {
        $this->holidayScheduleService->deleteHolidaySchedule($off_date);

        return redirect()->route('admin.off-dates.index')
                         ->with('success', 'Public Holiday deleted successfully.');
    }
}
