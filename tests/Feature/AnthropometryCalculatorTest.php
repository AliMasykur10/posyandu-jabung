<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Measurement;
use App\Services\AnthropometryCalculator;
use Carbon\Carbon;
use Database\Seeders\NutritionStandardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnthropometryCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NutritionStandardSeeder::class);
    }

    public function test_it_matches_the_official_who_anthro_validation_examples(): void
    {
        $examples = [
            ['L', 1001, 18.0, 120.0, [2.20, 7.31, -2.39, -3.01]],
            ['P', 1000, 15.0, 80.0, [0.95, -3.50, 4.13, 4.66]],
            ['L', 1010, 10.0, 100.0, [-2.76, 1.62, -5.19, -5.61]],
            ['L', 1000, 15.0, 100.0, [0.69, 1.70, -0.29, -0.58]],
        ];

        $calculator = app(AnthropometryCalculator::class);
        $measurementDate = Carbon::parse('2024-01-01');

        foreach ($examples as [$gender, $ageDays, $weight, $height, $expected]) {
            $child = new Child([
                'gender' => $gender,
                'birth_date' => $measurementDate->copy()->subDays($ageDays)->toDateString(),
            ]);
            $result = $calculator->calculate(
                $child,
                $weight,
                $height,
                Measurement::METHOD_HEIGHT,
                $measurementDate
            );

            $this->assertSame($expected[0], $result['bb_u_zscore']);
            $this->assertSame($expected[1], $result['tb_u_zscore']);
            $this->assertSame($expected[2], $result['bb_tb_zscore']);
            $this->assertSame($expected[3], $result['imt_u_zscore']);
        }
    }

    public function test_it_applies_the_point_seven_centimetre_measurement_correction(): void
    {
        $calculator = app(AnthropometryCalculator::class);
        $measurementDate = Carbon::parse('2024-01-01');
        $child = new Child([
            'gender' => 'P',
            'birth_date' => $measurementDate->copy()->subDays(365)->toDateString(),
        ]);

        $recumbent = $calculator->calculate(
            $child,
            8.5,
            75.0,
            Measurement::METHOD_LENGTH,
            $measurementDate
        );
        $standing = $calculator->calculate(
            $child,
            8.5,
            74.3,
            Measurement::METHOD_HEIGHT,
            $measurementDate
        );

        $this->assertSame(75.0, $recumbent['standardized_height']);
        $this->assertSame(75.0, $standing['standardized_height']);
        $this->assertSame($recumbent['bb_tb_zscore'], $standing['bb_tb_zscore']);
        $this->assertSame($recumbent['imt_u_zscore'], $standing['imt_u_zscore']);
    }
}
