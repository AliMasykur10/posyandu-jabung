<?php

namespace Database\Seeders;

use App\Models\NutritionStandard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NutritionStandardSeeder extends Seeder
{
    private const FILES = [
        'weianthro.txt' => [NutritionStandard::WEIGHT_FOR_AGE, 'age'],
        'lenanthro.txt' => [NutritionStandard::LENGTH_HEIGHT_FOR_AGE, 'age'],
        'bmianthro.txt' => [NutritionStandard::BMI_FOR_AGE, 'age'],
        'wflanthro.txt' => [NutritionStandard::WEIGHT_FOR_LENGTH, 'length'],
        'wfhanthro.txt' => [NutritionStandard::WEIGHT_FOR_HEIGHT, 'height'],
    ];

    public function run(): void
    {
        $now = now();
        $sourceVersion = config('anthropometry.source_version');

        foreach (self::FILES as $filename => [$indicator, $referenceColumn]) {
            $path = database_path('data/who/'.$filename);

            if (! is_file($path)) {
                throw new RuntimeException("Berkas standar WHO tidak ditemukan: {$filename}");
            }

            $handle = fopen($path, 'rb');
            $headers = str_getcsv((string) fgets($handle), "\t");
            $batch = [];

            while (($line = fgets($handle)) !== false) {
                $values = str_getcsv(trim($line), "\t");
                $row = array_combine($headers, $values);

                $batch[] = [
                    'indicator' => $indicator,
                    'gender' => (int) $row['sex'] === 1 ? 'L' : 'P',
                    'reference_value' => (float) $row[$referenceColumn],
                    'l' => (float) $row['l'],
                    'm' => (float) $row['m'],
                    's' => (float) $row['s'],
                    'source_version' => $sourceVersion,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) === 1000) {
                    $this->upsert($batch);
                    $batch = [];
                }
            }

            fclose($handle);

            if ($batch !== []) {
                $this->upsert($batch);
            }
        }
    }

    private function upsert(array $rows): void
    {
        DB::table('nutrition_standards')->upsert(
            $rows,
            ['indicator', 'gender', 'reference_value'],
            ['l', 'm', 's', 'source_version', 'updated_at']
        );
    }
}
