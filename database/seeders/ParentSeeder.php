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
            'posyandu_id'  => 1, // Pastikan ID ini ada di tabel posyandus
            'no_kk'        => '1234567890123456', // Wajib 16 digit
            'mother_name'  => 'Siti Aminah',
            'father_name'  => 'Budi Utomo',
            'phone_number' => '08123456789',
            'address'      => 'Desa Jabung Sisir',
            'rt'           => '001',
            'rw'           => '002',
        ]);
    }
}
