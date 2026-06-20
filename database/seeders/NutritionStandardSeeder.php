<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NutritionStandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Gender 'L', Umur (Bulan), -3SD, -2SD, Median, +1SD
            ['L', 0, 2.1, 2.5, 3.3, 3.9],
            ['L', 1, 2.9, 3.4, 4.5, 5.1],
            ['L', 2, 3.8, 4.3, 5.6, 6.3],
            ['L', 3, 4.4, 5.0, 6.4, 7.2],
            ['L', 4, 4.9, 5.6, 7.0, 7.8],
            ['L', 5, 5.3, 6.0, 7.5, 8.4],
            ['L', 6, 5.7, 6.4, 7.9, 8.8],
            ['L', 7, 5.9, 6.7, 8.3, 9.2],
            ['L', 8, 6.2, 7.0, 8.6, 9.6],
            ['L', 9, 6.4, 7.2, 8.9, 9.9],
            ['L', 10, 6.6, 7.4, 9.2, 10.2],
            ['L', 11, 6.8, 7.6, 9.4, 10.5],
            ['L', 12, 6.9, 7.7, 9.6, 10.8],
            // ==================== DATA ANAK PEREMPUAN (P) ====================
            // Gender 'P', Umur (Bulan), -3SD, -2SD, Median, +1SD
            ['P', 0, 2.0, 2.4, 3.2, 3.8],
            ['P', 1, 2.7, 3.2, 4.2, 4.8],
            ['P', 2, 3.4, 3.9, 5.1, 5.8],
            ['P', 3, 4.0, 4.5, 5.8, 6.6],
            ['P', 4, 4.4, 5.0, 6.4, 7.3],
            ['P', 5, 4.8, 5.4, 6.9, 7.8],
            ['P', 6, 5.1, 5.7, 7.3, 8.2],
            ['P', 7, 5.3, 6.0, 7.6, 8.6],
            ['P', 8, 5.6, 6.3, 7.9, 9.0],
            ['P', 9, 5.8, 6.5, 8.2, 9.3],
            ['P', 10, 5.9, 6.7, 8.5, 9.6],
            ['P', 11, 6.1, 6.9, 8.7, 9.9],
            ['P', 12, 6.3, 7.0, 8.9, 10.1],
        ];

        foreach ($data as $item) {
            DB::table('nutrition_standards')->updateOrInsert(
                [
                    'gender' => $item[0],
                    'age_month' => $item[1],
                ],
                [
                    'min_3sd' => $item[2],
                    'min_2sd' => $item[3],
                    'median' => $item[4],
                    'plus_1sd' => $item[5],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
