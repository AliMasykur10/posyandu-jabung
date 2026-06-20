<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Measurement;
use App\Models\NutritionStandard;
use Carbon\Carbon;
use InvalidArgumentException;

class AnthropometryCalculator
{
    private array $standards = [];

    public function calculate(
        Child $child,
        float $weight,
        float $height,
        string $measurementMethod,
        string|Carbon $measurementDate
    ): array {
        $date = $measurementDate instanceof Carbon
            ? $measurementDate->copy()->startOfDay()
            : Carbon::parse($measurementDate)->startOfDay();
        $birthDate = Carbon::parse($child->birth_date)->startOfDay();

        if ($date->lt($birthDate)) {
            throw new InvalidArgumentException('Tanggal pengukuran tidak boleh sebelum tanggal lahir.');
        }

        if (! in_array($measurementMethod, [Measurement::METHOD_LENGTH, Measurement::METHOD_HEIGHT], true)) {
            throw new InvalidArgumentException('Metode pengukuran panjang atau tinggi badan tidak valid.');
        }

        $ageDays = (int) round($birthDate->diffInDays($date));
        $ageMonths = $ageDays / (float) config('anthropometry.days_per_month');

        if ($ageMonths >= 60 || $ageDays > (int) config('anthropometry.maximum_age_days')) {
            throw new InvalidArgumentException('Standar antropometri hanya tersedia untuk anak usia 0 sampai 59 bulan.');
        }

        $standardizedHeight = $this->standardizeHeight($height, $measurementMethod, $ageDays);
        $bmi = $weight / (($standardizedHeight / 100) ** 2);
        $gender = $child->gender;

        $bbU = $this->ageBasedZScore(
            NutritionStandard::WEIGHT_FOR_AGE,
            $gender,
            $ageDays,
            $weight,
            true
        );
        $tbU = $this->ageBasedZScore(
            NutritionStandard::LENGTH_HEIGHT_FOR_AGE,
            $gender,
            $ageDays,
            $standardizedHeight,
            false
        );
        $imtU = $this->ageBasedZScore(
            NutritionStandard::BMI_FOR_AGE,
            $gender,
            $ageDays,
            $bmi,
            true
        );
        $bbTbIndicator = $ageDays < 731
            ? NutritionStandard::WEIGHT_FOR_LENGTH
            : NutritionStandard::WEIGHT_FOR_HEIGHT;
        $bbTb = $this->measurementBasedZScore(
            $bbTbIndicator,
            $gender,
            $standardizedHeight,
            $weight
        );

        return [
            'measurement_method' => $measurementMethod,
            'age_days' => $ageDays,
            'standardized_height' => round($standardizedHeight, 2),
            'bmi' => round($bmi, 2),
            'bb_u_zscore' => $bbU,
            'bb_u_flagged' => $bbU < -6 || $bbU > 5,
            'bb_u_status' => $this->classifyWeightForAge($bbU),
            'tb_u_zscore' => $tbU,
            'tb_u_flagged' => abs($tbU) > 6,
            'tb_u_status' => $this->classifyHeightForAge($tbU),
            'bb_tb_zscore' => $bbTb,
            'bb_tb_flagged' => abs($bbTb) > 5,
            'bb_tb_status' => $this->classifyWeightForHeight($bbTb),
            'imt_u_zscore' => $imtU,
            'imt_u_flagged' => abs($imtU) > 5,
            'imt_u_status' => $this->classifyWeightForHeight($imtU),
            'calculation_version' => config('anthropometry.calculation_version'),
        ];
    }

    private function standardizeHeight(float $height, string $method, int $ageDays): float
    {
        if ($ageDays < 731 && $method === Measurement::METHOD_HEIGHT) {
            return $height + 0.7;
        }

        if ($ageDays >= 731 && $method === Measurement::METHOD_LENGTH) {
            return $height - 0.7;
        }

        return $height;
    }

    private function ageBasedZScore(
        string $indicator,
        string $gender,
        int $ageDays,
        float $measure,
        bool $adjustExtreme
    ): float {
        $standard = $this->standard($indicator, $gender, (float) $ageDays);

        if (! $standard) {
            throw new InvalidArgumentException("Standar {$indicator} tidak ditemukan untuk usia {$ageDays} hari.");
        }

        return round($this->zScore($measure, $standard, $adjustExtreme), 2);
    }

    private function measurementBasedZScore(
        string $indicator,
        string $gender,
        float $referenceValue,
        float $measure
    ): float {
        $minimum = $indicator === NutritionStandard::WEIGHT_FOR_LENGTH ? 45 : 65;
        $maximum = $indicator === NutritionStandard::WEIGHT_FOR_LENGTH ? 110 : 120;

        if ($referenceValue < $minimum || $referenceValue > $maximum) {
            throw new InvalidArgumentException("Panjang atau tinggi badan berada di luar rentang standar {$minimum}-{$maximum} cm.");
        }

        $lower = floor($referenceValue * 10) / 10;
        $upper = ceil($referenceValue * 10) / 10;
        $lowerStandard = $this->standard($indicator, $gender, $lower);
        $upperStandard = $this->standard($indicator, $gender, $upper);

        if (! $lowerStandard || ! $upperStandard) {
            throw new InvalidArgumentException('Standar BB/PB atau BB/TB tidak ditemukan.');
        }

        $ratio = $upper === $lower ? 0 : ($referenceValue - $lower) / ($upper - $lower);
        $interpolated = (object) [
            'l' => $lowerStandard->l + ($ratio * ($upperStandard->l - $lowerStandard->l)),
            'm' => $lowerStandard->m + ($ratio * ($upperStandard->m - $lowerStandard->m)),
            's' => $lowerStandard->s + ($ratio * ($upperStandard->s - $lowerStandard->s)),
        ];

        return round($this->zScore($measure, $interpolated, true), 2);
    }

    private function zScore(float $measure, object $standard, bool $adjustExtreme): float
    {
        $l = (float) $standard->l;
        $m = (float) $standard->m;
        $s = (float) $standard->s;
        $zScore = abs($l) < 0.0000001
            ? log($measure / $m) / $s
            : ((($measure / $m) ** $l) - 1) / ($s * $l);

        if (! $adjustExtreme || ($zScore >= -3 && $zScore <= 3)) {
            return $zScore;
        }

        $sdValue = fn (int $sd): float => $m * ((1 + ($l * $s * $sd)) ** (1 / $l));
        $sd3Positive = $sdValue(3);
        $sd3Negative = $sdValue(-3);

        if ($zScore > 3) {
            return 3 + (($measure - $sd3Positive) / ($sd3Positive - $sdValue(2)));
        }

        return -3 + (($measure - $sd3Negative) / ($sdValue(-2) - $sd3Negative));
    }

    private function standard(string $indicator, string $gender, float $referenceValue): ?NutritionStandard
    {
        $key = $indicator.'-'.$gender;

        if (! isset($this->standards[$key])) {
            $this->standards[$key] = NutritionStandard::where('indicator', $indicator)
                ->where('gender', $gender)
                ->get()
                ->keyBy(fn (NutritionStandard $standard) => number_format($standard->reference_value, 1, '.', ''));
        }

        return $this->standards[$key]->get(number_format($referenceValue, 1, '.', ''));
    }

    private function classifyWeightForAge(float $zScore): string
    {
        return match (true) {
            $zScore < -3 => Measurement::BB_U_SEVERELY_UNDERWEIGHT,
            $zScore < -2 => Measurement::BB_U_UNDERWEIGHT,
            $zScore <= 1 => Measurement::BB_U_NORMAL,
            default => Measurement::BB_U_OVERWEIGHT_RISK,
        };
    }

    private function classifyHeightForAge(float $zScore): string
    {
        return match (true) {
            $zScore < -3 => Measurement::TB_U_SEVERELY_STUNTED,
            $zScore < -2 => Measurement::TB_U_STUNTED,
            $zScore <= 3 => Measurement::TB_U_NORMAL,
            default => Measurement::TB_U_TALL,
        };
    }

    private function classifyWeightForHeight(float $zScore): string
    {
        return match (true) {
            $zScore < -3 => Measurement::BB_TB_SEVERELY_WASTED,
            $zScore < -2 => Measurement::BB_TB_WASTED,
            $zScore <= 1 => Measurement::BB_TB_NORMAL,
            $zScore <= 2 => Measurement::BB_TB_OVERWEIGHT_RISK,
            $zScore <= 3 => Measurement::BB_TB_OVERWEIGHT,
            default => Measurement::BB_TB_OBESE,
        };
    }
}
