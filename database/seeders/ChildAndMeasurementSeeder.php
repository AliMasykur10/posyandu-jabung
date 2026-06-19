<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Child;
use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChildAndMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat data Posyandu (Gunakan 'name' dan 'address' sesuai migrasi)
        $posyandu = \App\Models\Posyandu::updateOrCreate(
            ['name' => 'Posyandu Melati'], // Kunci pencarian
            [
                'address' => 'Desa Jabung, Probolinggo',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $posyanduId = $posyandu->id;

        // 2. Buat data Orang Tua 
        // CATATAN: Pastikan kolom di bawah ini (mother_name, father_name, address) 
        // sudah sesuai dengan file migrasi parent_details kamu!
        $parent = \App\Models\ParentDetail::updateOrCreate(
            ['no_kk' => '1234567890123456'], // Kunci pencarian
            [
                'posyandu_id' => $posyanduId,
                'mother_name' => 'Siti Aminah',
                'father_name' => 'Budi Santoso',
                'address'     => 'Desa Jabung RT 01',
                'rt'          => '001',
                'rw'          => '001',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
        $parentId = $parent->id;

        // 3. Buat Data Anak (Gunakan 'birth_date' dan 'birth_weight' sesuai migrasi sebelumnya)
        $ali = Child::create([
            'posyandu_id' => $posyanduId,
            'parent_id' => $parentId,
            'name' => 'Ali',
            'birth_date' => Carbon::now()->subMonths(6),
            'gender' => 'L',
            'birth_weight' => 3.20,
        ]);

        // 3. Buat Data Anak: Masykur
        $masykur = Child::create([
            'posyandu_id' => $posyanduId,
            'parent_id' => $parentId,
            'name' => 'Masykur',
            'birth_date' => Carbon::now()->subMonths(3),
            'gender' => 'L',
            'birth_weight' => 3.00,
        ]);

        // 4. Buat Data Pengukuran Dummy untuk Ali (Tren Naik)
        $dataAli = [
            ['berat' => 3.5, 'bulan' => -6],
            ['berat' => 4.5, 'bulan' => -5],
            ['berat' => 5.5, 'bulan' => -4],
            ['berat' => 6.5, 'bulan' => -3],
            ['berat' => 7.5, 'bulan' => -2],
            ['berat' => 8.2, 'bulan' => -1],
        ];

        foreach ($dataAli as $d) {
            Measurement::create([
                'child_id' => $ali->id,
                'weight' => $d['berat'],
                'height' => 50 + ($d['berat'] * 2),
                'measurement_date' => Carbon::now()->addMonths($d['bulan']),
                'status' => 'Gizi Baik (Normal)'
            ]);
        }

        // 5. Buat Data Pengukuran Dummy untuk Masykur (Gizi Kurang)
        Measurement::create([
            'child_id' => $masykur->id,
            'weight' => 2.2,
            'height' => 45,
            'measurement_date' => Carbon::now(),
            'status' => 'Gizi Kurang'
        ]);
    }
}
