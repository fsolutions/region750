<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = Role::where('slug', 'administrator')->first();
        $client = Role::where('slug', 'client')->first();
        $manager = Role::where('slug', 'manager')->first();
        $master = Role::where('slug', 'master')->first();
        $intern = Role::where('slug', 'intern')->first();
        $accountant = Role::where('slug', 'accountant')->first();

        $user0 = User::updateOrCreate(
            ['email' => 'fomichevms@gmail.com'],
            [
                'name' => 'Администратор',
                'phone' => '79150670468',
                'position' => 'Администратор',
                'password' => bcrypt('addqdd555')
            ]
        );
        $user0->roles()->sync([$administrator->id]);

        $user1 = User::updateOrCreate(
            ['email' => 'manager@region750.ru'],
            [
                'name' => 'Менеджер Менеджеров',
                'phone' => '79150670469',
                'position' => 'Менеджер',
                'password' => bcrypt('addqdd555')
            ]
        );
        $user1->roles()->sync([$manager->id]);

        $user2 = User::updateOrCreate(
            ['email' => 'master@region750.ru'],
            [
                'name' => 'Мастер Мастеров',
                'phone' => '79150670470',
                'position' => 'Мастер',
                'password' => bcrypt('addqdd555')
            ]
        );
        $user2->roles()->sync([$master->id]);

        $user3 = User::updateOrCreate(
            ['email' => 'intern@region750.ru'],
            [
                'name' => 'Стажер Стажеров',
                'phone' => '79150670471',
                'position' => 'Стажер',
                'password' => bcrypt('addqdd555')
            ]
        );
        $user3->roles()->sync([$intern->id]);

        // $user4 = User::updateOrCreate(
        //     ['email' => 'accountant@region750.ru'],
        //     ['name' => 'Стажер Стажеров',
        //     'phone' => '79150670471',
        //     'position' => 'Стажер',
        //     'password' => bcrypt('addqdd555')]);
        // $user4->roles()->sync([$accountant->id]);

        // Тестовый клиент
        $user20 = User::updateOrCreate(
            ['email' => 'client_test@region750.ru'],
            [
                'name' => 'Сергей Иванович Клиент',
                'phone' => '79150670472',
                'position' => '',
                'password' => bcrypt('addqdd555')
            ]
        );
        $user20->roles()->sync([$client->id]);
    }
}
