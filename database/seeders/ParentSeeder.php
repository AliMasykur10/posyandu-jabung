<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ParentDetail::create([
        'father_name' => 'Budi Utomo',
        'mother_name' => 'Siti Aminah',
        'phone_number'=> '08123456789',
        'address'     => 'Desa Jabung Sisir RT 01'
    ]);
    }
}
