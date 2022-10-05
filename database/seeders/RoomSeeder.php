<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rooms')->insert([
            // Cabin Start
            [
                'id' => 1,
                'name' => 'Cabin A',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin A',
                'type' => 'cabin'
            ],
            [
                'id' => 2,
                'name' => 'Cabin B',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin B',
                'type' => 'cabin'
            ],
            [
                'id' => 3,
                'name' => 'Cabin C',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin C',
                'type' => 'cabin'
            ],
            [
                'id' => 4,
                'name' => 'Cabin D',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin D',
                'type' => 'cabin'
            ],
            [
                'id' => 5,
                'name' => 'Cabin E',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin E',
                'type' => 'cabin'
            ],
            [
                'id' => 6,
                'name' => 'Cabin F',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin F',
                'type' => 'cabin'
            ],
            [
                'id' => 7,
                'name' => 'Cabin G',
                'sex' => 'c',
                'size' => 0,
                'location' => 'Cabin G',
                'type' => 'cabin'
            ],
            // Cabin End
            // VIP Start
            [
                'id' => 8,
                'name' => 'VIP Room 15',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 15',
                'type' => 'vip'
            ],
            [
                'id' => 9,
                'name' => 'VIP Room 13',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 13',
                'type' => 'vip'
            ],
            [
                'id' => 10,
                'name' => 'VIP Room 11',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 11',
                'type' => 'vip'
            ],
            [
                'id' => 11,
                'name' => 'VIP Room 9',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 9',
                'type' => 'vip'
            ],
            [
                'id' => 12,
                'name' => 'VIP Room 7',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 7',
                'type' => 'vip'
            ],
            [
                'id' => 13,
                'name' => 'VIP Room 6',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 6',
                'type' => 'vip'
            ],
            [
                'id' => 14,
                'name' => 'VIP Room 4',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 4',
                'type' => 'vip'
            ],
            [
                'id' => 15,
                'name' => 'VIP Room 2',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 2',
                'type' => 'vip'
            ],
            [
                'id' => 16,
                'name' => 'VIP Room 16',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 16',
                'type' => 'vip'
            ],
            [
                'id' => 17,
                'name' => 'VIP Room 14',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 14',
                'type' => 'vip'
            ],
            [
                'id' => 18,
                'name' => 'VIP Room 12',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 12',
                'type' => 'vip'
            ],
            [
                'id' => 19,
                'name' => 'VIP Room 10',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 10',
                'type' => 'vip'
            ],
            [
                'id' => 20,
                'name' => 'VIP Room 8',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 8',
                'type' => 'vip'
            ],
            [
                'id' => 21,
                'name' => 'VIP Room 5',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 5',
                'type' => 'vip'
            ],
            [
                'id' => 22,
                'name' => 'VIP Room 3',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 3',
                'type' => 'vip'
            ],
            [
                'id' => 23,
                'name' => 'VIP Room 1',
                'sex' => 'c',
                'size' => 0,
                'location' => 'VIP Room 1',
                'type' => 'vip'
            ],
            // VIP End
            // Dorm
            [
                'id' => 24,
                'name' => 'Men\'s Dorm 308',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 308',
                'type' => 'dorm'
            ],
            [
                'id' => 25,
                'name' => 'Men\'s Dorm 306',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 306',
                'type' => 'dorm'
            ],
            [
                'id' => 26,
                'name' => 'Men\'s Dorm 304',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 304',
                'type' => 'dorm'
            ],
            [
                'id' => 27,
                'name' => 'Men\'s Dorm 302',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 302',
                'type' => 'dorm'
            ],
            [
                'id' => 28,
                'name' => 'Men\'s Dorm 300',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 300',
                'type' => 'dorm'
            ],
            [
                'id' => 29,
                'name' => 'Men\'s Dorm 311',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 311',
                'type' => 'dorm'
            ],
            [
                'id' => 30,
                'name' => 'Men\'s Dorm 313',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 313',
                'type' => 'dorm'
            ],
            [
                'id' => 31,
                'name' => 'Men\'s Dorm 315',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 315',
                'type' => 'dorm'
            ],
            [
                'id' => 32,
                'name' => 'Men\'s Dorm 317',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 317',
                'type' => 'dorm'
            ],
            [
                'id' => 33,
                'name' => 'Men\'s Dorm 319',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 319',
                'type' => 'dorm'
            ],
            [
                'id' => 34,
                'name' => 'Men\'s Dorm 309',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 309',
                'type' => 'dorm'
            ],
            [
                'id' => 35,
                'name' => 'Men\'s Dorm 307',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 307',
                'type' => 'dorm'
            ],
            [
                'id' => 36,
                'name' => 'Men\'s Dorm 305',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 305',
                'type' => 'dorm'
            ],
            [
                'id' => 37,
                'name' => 'Men\'s Dorm 303',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 303',
                'type' => 'dorm'
            ],
            [
                'id' => 38,
                'name' => 'Men\'s Dorm 301',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 301',
                'type' => 'dorm'
            ],
            [
                'id' => 39,
                'name' => 'Men\'s Dorm 310',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 310',
                'type' => 'dorm'
            ],
            [
                'id' => 40,
                'name' => 'Men\'s Dorm 312',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 312',
                'type' => 'dorm'
            ],
            [
                'id' => 41,
                'name' => 'Men\'s Dorm 314',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 314',
                'type' => 'dorm'
            ],
            [
                'id' => 42,
                'name' => 'Men\'s Dorm 316',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 316',
                'type' => 'dorm'
            ],
            [
                'id' => 43,
                'name' => 'Men\'s Dorm 318',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 318',
                'type' => 'dorm'
            ],
            // End Men's Dorm
            // Start Women's Dorm
            [
                'id' => 44,
                'name' => 'Men\'s Dorm 318',
                'sex' => 'm',
                'size' => 0,
                'location' => 'Men\'s Dorm 318',
                'type' => 'dorm'
            ],
        ]);
    }
}
