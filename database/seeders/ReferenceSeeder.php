<?php

namespace Database\Seeders;

use App\Models\Reference;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    public $references = [
        ['name' => 'Услуги по договору'],       // ID = 1
        ['name' => 'Типы приборов'],            // ID = 2
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // добавление справочников
        // добавляет спровочник, если его нет таблице
        foreach ($this->references as $key => $reference) :
            Reference::firstOrCreate([
                'name' => $reference['name'],
                'parent_id' => (isset($reference['parent_id'])) ? $reference['parent_id'] : 0,
            ]);
        endforeach;

        // добавление slug
        $references = Reference::all();

        foreach ($references as $key => $reference) :
            $reference->slug = Str::slug($reference->name, '-');
            $reference->save();
        endforeach;
    }
}
