<?php

namespace Database\Seeders;


use App\Models\OpeningDay;
use Illuminate\Database\Seeder;

class OpeningDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        for($i=1; $i<=3; $i++){
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $data = [];
            //\DB::beginTransaction();
            foreach($days as $day){
                $data['day_of_week'] =  $day;
                $data['start_time'] =  '00:00:00';
                $data['end_time'] =  '09:00:00';
                $data['total_mins'] = 0;
                $data['status'] = true;
                OpeningDay::firstOrCreate($data);

            }
        }
    }
}
