<?php

namespace Database\Seeders;

use App\Models\Posyandu;
use Illuminate\Database\Seeder;

class PosyanduSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        $data = [
            ['name' => 'Posyandu Melati', 'address' => 'Jabung Sisir'],
            ['name' => 'Posyandu Mawar', 'address' => 'Jabung Sisir'],
            ['name' => 'Posyandu Kenanga', 'address' => 'Jabung Sisir'],
            ['name' => 'Posyandu Dahlia', 'address' => 'Jabung Sisir'],
            ['name' => 'Posyandu Anggrek', 'address' => 'Jabung Sisir'],
        ];

        foreach ($data as $item) {
            Posyandu::firstOrCreate(
                ['name' => $item['name']],
                ['address' => $item['address']]
            );
        }
    }
}
