<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReferenceProperty;

class ReferencePropertySeeder extends Seeder
{
    public $properties = [
        ['name' => 'Замена газового крана', 'ordering_number' => 10, 'reference_id' => 1],
        ['name' => 'Замена газового шланга', 'ordering_number' => 20, 'reference_id' => 1],
        ['name' => 'Подключение плиты', 'ordering_number' => 30, 'reference_id' => 1],
        ['name' => 'Подключение проточного газового нагревателя', 'ordering_number' => 40, 'reference_id' => 1],
        ['name' => 'Чистка форсунок', 'ordering_number' => 50, 'reference_id' => 1],
        ['name' => 'Замена термопары (плита, колонка)', 'ordering_number' => 60, 'reference_id' => 1],
        ['name' => 'Запись на повторное техническое обслуживание', 'ordering_number' => 70, 'reference_id' => 1],
        ['name' => 'Другое', 'ordering_number' => 80, 'reference_id' => 1],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->properties as $key => $value) :
            ReferenceProperty::firstOrCreate([
                'name' => $value['name'],
                'reference_id' => $value['reference_id'],
                'need_reference' => (isset($value['need_reference']) ? $value['need_reference'] : 0),
                'ordering_number' => (isset($value['ordering_number']) ? $value['ordering_number'] : 10)
            ]);
        endforeach;
    }
}
