<?php
namespace App\Services;

use App\Models\HolidaySchedule;


class HolidayScheduleService
{

     /**
     * Get all public holidays with pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllHolidaySchedules()
    {
        return HolidaySchedule::latest()->paginate(10);
    }

    /**
     * Create a new public holiday.
     *
     * @param array $data
     * @return \App\Models\HolidaySchedule
     */
    public function createHolidaySchedule(array $data): HolidaySchedule
    {
        $data['status'] = isset($data['status']);
        return HolidaySchedule::create($data);
    }

    /**
     * Update an existing public holiday.
     *
     * @param \App\Models\HolidaySchedule $HolidaySchedule
     * @param array $data
     * @return \App\Models\HolidaySchedule
     */
    public function updateHolidaySchedule(HolidaySchedule $holidaySchedule, array $data): HolidaySchedule
    {
        $data['status'] = isset($data['status']);
        $holidaySchedule->update($data);
        return $holidaySchedule;
    }

    /**
     * Delete a public holiday.
     *
     * @param \App\Models\HolidaySchedule $holidaySchedule
     * @return void
     */
    public function deleteHolidaySchedule(HolidaySchedule $holidaySchedule): void
    {
        $holidaySchedule->delete();
    }

    public function checkExistingOffDateEntry($date) : bool
    {
        $date = date('Y-m-d', strtotime($date));
        try{
            $offDate = HolidaySchedule::WhereDate('date_to', '=', $date)
                ->where('status', true)->latest()->exists();
            return $offDate;
         }catch(\Exception $e){
            return false;
         } 
    }
}