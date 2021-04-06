<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = Role::firstOrCreate([
            'name' => 'Администратор',
            'slug' => 'administrator',
        ]);

        $client = Role::firstOrCreate([
            'name' => 'Клиент',
            'slug' => 'client',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Менеджер',
            'slug' => 'manager',
        ]);

        $master = Role::firstOrCreate([
            'name' => 'Мастер',
            'slug' => 'master',
        ]);

        $intern = Role::firstOrCreate([
            'name' => 'Стажер',
            'slug' => 'intern',
        ]);

        // $accountant = Role::firstOrCreate([
        //     'name' => 'Бухгалтер',
        //     'slug' => 'accountant',
        // ]);
    }
}
