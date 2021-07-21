<?php

namespace Database\Seeders;

use App\Models\House;
use App\Models\TOVDGO;
use App\Models\TOVentilation;
use Illuminate\Database\Seeder;

class TOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $houses = House::all();

        // Уравняем годы для более простых расчетов
        foreach ($houses as $house) {
            if ($house->build_year < 1983) {
                $house->build_year = 1983;
                $house->save();
            }
        }

        // Через 30 лет после стройки
        foreach ($houses as $house) {
            $newHouse = false;

            $randData = mt_rand(1610700996, 1640422596);
            $finalDate = date('Y-m-d', $randData);
            $finalDateArray = explode("-", $finalDate);

            $nowDateTime = time();
            $firstTODate = 2021 . '-' . $finalDateArray[1] . '-' . $finalDateArray[2];
            $secondTODate = 2022 . '-' . $finalDateArray[1] . '-' . $finalDateArray[2];

            $firstDT = strtotime($firstTODate);
            if ($firstDT >= $nowDateTime) {
                $firstTODate = 2020 . '-' . $finalDateArray[1] . '-' . $finalDateArray[2];
                $secondTODate = 2021 . '-' . $finalDateArray[1] . '-' . $finalDateArray[2];
            }

            $TOVDGO_FIRST = TOVDGO::create([
                'vgko_region_id' => $house->region_id,
                'vgko_city_id' => $house->city_id,
                'vgko_street_id' => $house->street_id,
                'vgko_house_id' => $house->id,
                'vgko_master_user_id' => 3,
                'vgko_master_user_id_2' => 4,
                'vgko_status' => 'Проведено',
                'vgko_date_of_work' => $firstTODate . ' 00:00:00'
            ]);

            $TOVentilation_FIRST = TOVentilation::create([
                'ventilation_region_id' => $house->region_id,
                'ventilation_city_id' => $house->city_id,
                'ventilation_street_id' => $house->street_id,
                'ventilation_house_id' => $house->id,
                'ventilation_master_user_id' => 3,
                'ventilation_master_user_id_2' => 4,
                'ventilation_status' => 'Проведено',
                'ventilation_date_of_work' => $firstTODate . ' 00:00:00'
            ]);

            $TOVDGO_PLANNED = TOVDGO::create([
                'vgko_region_id' => $house->region_id,
                'vgko_city_id' => $house->city_id,
                'vgko_street_id' => $house->street_id,
                'vgko_house_id' => $house->id,
                'vgko_master_user_id' => 3,
                'vgko_master_user_id_2' => 4,
                'vgko_status' => 'Запланировано',
                'vgko_date_of_work' => $secondTODate . ' 00:00:00'
            ]);

            $TOVentilation_PLANNED = TOVentilation::create([
                'ventilation_region_id' => $house->region_id,
                'ventilation_city_id' => $house->city_id,
                'ventilation_street_id' => $house->street_id,
                'ventilation_house_id' => $house->id,
                'ventilation_master_user_id' => 3,
                'ventilation_master_user_id_2' => 4,
                'ventilation_status' => 'Запланировано',
                'ventilation_date_of_work' => $secondTODate . ' 00:00:00'
            ]);
        }
    }
}
