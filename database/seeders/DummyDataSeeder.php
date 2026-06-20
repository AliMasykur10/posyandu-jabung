<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\NutritionStandard;
use App\Models\ParentDetail;
use App\Models\Posyandu;
use App\Models\User;
use App\Services\AnthropometryCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DummyDataSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    private const FAMILIES_PER_POSYANDU = 10;

    private const CHILDREN_PER_POSYANDU = 15;

    public function run(): void
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true)) {
            throw new RuntimeException('DummyDataSeeder hanya boleh dijalankan pada environment local atau testing.');
        }

        $posyandus = Posyandu::orderBy('id')->get();

        if ($posyandus->isEmpty()) {
            throw new RuntimeException('Data Posyandu belum tersedia. Buat data Posyandu terlebih dahulu.');
        }

        $accountRows = [];

        DB::transaction(function () use ($posyandus, &$accountRows) {
            $this->clearOperationalData();
            $this->call([
                NutritionStandardSeeder::class,
                UserSeeder::class,
            ]);

            $accountRows = $this->createFamiliesAndHealthData($posyandus);
        });

        $this->showSummary($accountRows);
    }

    private function clearOperationalData(): void
    {
        Measurement::query()->delete();
        Child::query()->delete();
        ParentDetail::query()->delete();
        User::query()->delete();
        NutritionStandard::query()->delete();

        DB::table('sessions')->delete();
        DB::table('password_reset_tokens')->delete();
    }

    private function createFamiliesAndHealthData(Collection $posyandus): array
    {
        $motherNames = ['Siti', 'Aminah', 'Rina', 'Lestari', 'Nurul', 'Dewi', 'Fitri', 'Yuni', 'Rahma', 'Indah'];
        $fatherNames = ['Budi', 'Ahmad', 'Agus', 'Hendra', 'Joko', 'Rizal', 'Fajar', 'Wahyu', 'Imam', 'Dedi'];
        $familyNames = ['Santoso', 'Hidayat', 'Pratama', 'Saputra', 'Setiawan'];
        $childNames = ['Alya', 'Raka', 'Nabila', 'Fikri', 'Zahra', 'Rizky', 'Aisyah', 'Farhan', 'Nayla', 'Dimas', 'Salma', 'Arif', 'Hana', 'Ilham', 'Putri'];
        $ages = [6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36, 39, 42, 45, 48];
        $calculator = app(AnthropometryCalculator::class);
        $password = Hash::make(self::DEFAULT_PASSWORD);
        $accountRows = [
            ['Admin', 'admin@gmail.com', self::DEFAULT_PASSWORD],
            ['Bidan', 'bidan@posyandu.id', self::DEFAULT_PASSWORD],
        ];

        foreach ($posyandus->values() as $posyanduIndex => $posyandu) {
            $posyanduNumber = $posyanduIndex + 1;
            $familySurname = $familyNames[$posyanduIndex % count($familyNames)];
            $childIndex = 0;

            $kader = User::where('role', 'kader')
                ->where('posyandu_id', $posyandu->id)
                ->firstOrFail();
            $accountRows[] = ['Kader '.$posyandu->name, $kader->email, self::DEFAULT_PASSWORD];

            for ($familyIndex = 1; $familyIndex <= self::FAMILIES_PER_POSYANDU; $familyIndex++) {
                $globalFamilyIndex = ($posyanduIndex * self::FAMILIES_PER_POSYANDU) + $familyIndex;
                $noKk = sprintf('35%02d%02d%010d', $posyanduNumber, $familyIndex, $globalFamilyIndex);
                $motherName = $motherNames[$familyIndex - 1].' '.$familySurname;
                $fatherName = $fatherNames[$familyIndex - 1].' '.$familySurname;
                $rt = sprintf('%03d', $familyIndex);
                $rw = sprintf('%03d', $posyanduNumber);

                $parentUser = User::create([
                    'name' => $motherName,
                    'email' => $noKk.'@posyandu.id',
                    'password' => $password,
                    'role' => 'orangtua',
                    'posyandu_id' => $posyandu->id,
                ]);
                $parentUser->forceFill(['email_verified_at' => now()])->save();

                $parent = ParentDetail::create([
                    'user_id' => $parentUser->id,
                    'posyandu_id' => $posyandu->id,
                    'no_kk' => $noKk,
                    'nik_mother' => sprintf('36%02d%02d%010d', $posyanduNumber, $familyIndex, $globalFamilyIndex),
                    'nik_father' => sprintf('37%02d%02d%010d', $posyanduNumber, $familyIndex, $globalFamilyIndex),
                    'mother_name' => $motherName,
                    'father_name' => $fatherName,
                    'phone_number' => '08'.sprintf('%010d', 1300000000 + $globalFamilyIndex),
                    'address' => ($posyandu->address ?: 'Jabung Sisir').", RT {$rt}/RW {$rw}",
                    'rt' => $rt,
                    'rw' => $rw,
                ]);

                if ($familyIndex === 1) {
                    $accountRows[] = ['Orang tua '.$posyandu->name, $parentUser->email, self::DEFAULT_PASSWORD];
                }

                $childrenInFamily = $familyIndex <= 5 ? 2 : 1;

                for ($sequence = 1; $sequence <= $childrenInFamily; $sequence++) {
                    $childIndex++;
                    $ageMonths = $ages[$childIndex - 1];
                    $gender = $childIndex % 2 === 0 ? 'L' : 'P';

                    $child = Child::create([
                        'posyandu_id' => $posyandu->id,
                        'parent_id' => $parent->id,
                        'name' => $childNames[$childIndex - 1].' '.$familySurname,
                        'birth_date' => now()->startOfDay()->subMonths($ageMonths)->subDays($childIndex % 10),
                        'gender' => $gender,
                        'birth_weight' => 2.8 + (($childIndex % 6) * 0.15),
                    ]);

                    for ($monthsAgo = 5; $monthsAgo >= 1; $monthsAgo--) {
                        $this->createMeasurement($child, $calculator, $childIndex, $monthsAgo);
                    }

                    if ($childIndex <= 12) {
                        $this->createMeasurement($child, $calculator, $childIndex, 0);
                    }
                }
            }

            if ($childIndex !== self::CHILDREN_PER_POSYANDU) {
                throw new RuntimeException('Jumlah balita dummy per Posyandu tidak sesuai rancangan.');
            }
        }

        return $accountRows;
    }

    private function createMeasurement(
        Child $child,
        AnthropometryCalculator $calculator,
        int $childIndex,
        int $monthsAgo
    ): void {
        $measurementDate = $monthsAgo === 0
            ? now()->startOfDay()
            : now()->startOfMonth()->subMonths($monthsAgo)->addDays(9);
        $ageDays = (int) round(Carbon::parse($child->birth_date)->startOfDay()->diffInDays($measurementDate));
        $ageMonths = $ageDays / (float) config('anthropometry.days_per_month');
        $method = $ageDays < 731 ? Measurement::METHOD_LENGTH : Measurement::METHOD_HEIGHT;
        $heightZScore = match ($childIndex) {
            7 => -2.5,
            8 => -3.5,
            default => 0.0,
        };
        $heightStandard = NutritionStandard::where('indicator', NutritionStandard::LENGTH_HEIGHT_FOR_AGE)
            ->where('gender', $child->gender)
            ->where('reference_value', $ageDays)
            ->firstOrFail();
        $height = $this->valueAtZScore($heightStandard, $heightZScore);
        $weightIndicator = $ageDays < 731
            ? NutritionStandard::WEIGHT_FOR_LENGTH
            : NutritionStandard::WEIGHT_FOR_HEIGHT;
        $weightTargetZScore = $this->weightTargetZScore($childIndex, $monthsAgo);
        $weightStandard = $this->interpolatedStandard($weightIndicator, $child->gender, $height);
        $weight = $this->valueAtZScore($weightStandard, $weightTargetZScore);
        $anthropometry = $calculator->calculate(
            $child,
            round($weight, 2),
            round($height, 2),
            $method,
            $measurementDate
        );
        $isAtRisk = in_array($anthropometry['bb_tb_status'], [
            Measurement::BB_TB_SEVERELY_WASTED,
            Measurement::BB_TB_WASTED,
        ], true);

        Measurement::create(array_merge($anthropometry, [
            'child_id' => $child->id,
            'weight' => round($weight, 2),
            'height' => round($height, 2),
            'head_circumference' => round(34 + (($ageDays / 30.4375) * 0.3) + (($childIndex % 2) * 0.2), 2),
            'arm_circumference' => round(11 + (($ageDays / 30.4375) * 0.08) - ($isAtRisk ? 0.5 : 0), 2),
            'vitamin_a' => $ageMonths >= 6 ? ($ageMonths >= 12 ? 'Merah' : 'Biru') : null,
            'deworming_medicine' => $ageMonths >= 12,
            'pmt_status' => $isAtRisk || $childIndex % 4 === 0 ? 'Diberikan' : 'Belum diberikan',
            'status' => $anthropometry['bb_tb_status'],
            'measurement_date' => $measurementDate,
            'notes' => $isAtRisk
                ? 'Perlu pemantauan pertumbuhan dan konsultasi dengan bidan.'
                : 'Pertumbuhan dipantau sesuai jadwal Posyandu.',
        ]));
    }

    private function weightTargetZScore(int $childIndex, int $monthsAgo): float
    {
        if ($childIndex <= 8 || $childIndex >= 13) {
            return 0;
        }

        return match ($childIndex) {
            9 => $monthsAgo >= 2 ? 0 : -2.5,
            10 => -2.5,
            11 => $monthsAgo >= 2 ? -2.5 : -3.5,
            12 => $monthsAgo >= 3 ? 0 : 1.5,
            default => 0,
        };
    }

    private function interpolatedStandard(string $indicator, string $gender, float $reference): object
    {
        $lower = floor($reference * 10) / 10;
        $upper = ceil($reference * 10) / 10;
        $lowerStandard = NutritionStandard::where('indicator', $indicator)
            ->where('gender', $gender)
            ->where('reference_value', $lower)
            ->firstOrFail();
        $upperStandard = NutritionStandard::where('indicator', $indicator)
            ->where('gender', $gender)
            ->where('reference_value', $upper)
            ->firstOrFail();
        $ratio = $upper === $lower ? 0 : ($reference - $lower) / ($upper - $lower);

        return (object) [
            'l' => $lowerStandard->l + ($ratio * ($upperStandard->l - $lowerStandard->l)),
            'm' => $lowerStandard->m + ($ratio * ($upperStandard->m - $lowerStandard->m)),
            's' => $lowerStandard->s + ($ratio * ($upperStandard->s - $lowerStandard->s)),
        ];
    }

    private function valueAtZScore(object $standard, float $zScore): float
    {
        $l = (float) $standard->l;
        $m = (float) $standard->m;
        $s = (float) $standard->s;

        return abs($l) < 0.0000001
            ? $m * exp($s * $zScore)
            : $m * ((1 + ($l * $s * $zScore)) ** (1 / $l));
    }

    private function showSummary(array $accountRows): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->newLine();
        $this->command->info('Data dummy Posyandu berhasil dibuat.');
        $this->command->table(['Role', 'Email', 'Kata sandi'], $accountRows);
        $this->command->line(sprintf(
            'Ringkasan: %d keluarga, %d balita, %d pengukuran.',
            ParentDetail::count(),
            Child::count(),
            Measurement::count()
        ));
    }
}
