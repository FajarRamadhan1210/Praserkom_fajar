<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('events')->insert([
            [
                'title'         => 'Orchestra Classical Musik',
                'category'      => 'Musik',
                'ticket_price'  => '200000',
                'description'   => 'Musik Orchetra',
                'image'         => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'         => 'Orchestra Four Season Vivaldi',
                'category'      => 'Musik',
                'ticket_price'  => '250000',
                'description'   => 'Musik Orchetra Vivaldi',
                'image'         => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'         => 'Festival IMX',
                'category'      => 'Festival',
                'ticket_price'  => '100000',
                'description'   => 'Pameran Indonesia modification Expo',
                'image'         => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'         => 'Kelas Crypto Timothy',
                'category'      => 'Seminar',
                'ticket_price'  => '1000000',
                'description'   => 'Kelas Timothy Ronald',
                'image'         => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'         => 'Konser Gustixa',
                'category'      => 'Musik',
                'ticket_price'  => '1500000',
                'description'   => 'abc',
                'image'         => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
