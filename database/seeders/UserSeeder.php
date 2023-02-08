<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'Nik',
                'email' => 'lubnik2005@gmail.com',
                'password' => Hash::make('password')
            ]
        ]);
        DB::table('attendees')->insert([
            [
                'first_name' => 'Nik',
                'email' => 'lubnik2005@gmail.com',
                'last_name' => 'Lubyanoy',
                'password' => Hash::make('password'),
                'sex' => 'm'
            ]
        ]);
    }
}
