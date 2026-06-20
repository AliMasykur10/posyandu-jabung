<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    public const METHOD_LENGTH = 'length';

    public const METHOD_HEIGHT = 'height';

    public const BB_U_SEVERELY_UNDERWEIGHT = 'Berat Badan Sangat Kurang';

    public const BB_U_UNDERWEIGHT = 'Berat Badan Kurang';

    public const BB_U_NORMAL = 'Berat Badan Normal';

    public const BB_U_OVERWEIGHT_RISK = 'Risiko Berat Badan Lebih';

    public const TB_U_SEVERELY_STUNTED = 'Sangat Pendek';

    public const TB_U_STUNTED = 'Pendek';

    public const TB_U_NORMAL = 'Tinggi Badan Normal';

    public const TB_U_TALL = 'Tinggi';

    public const BB_TB_SEVERELY_WASTED = 'Gizi Buruk';

    public const BB_TB_WASTED = 'Gizi Kurang';

    public const BB_TB_NORMAL = 'Gizi Baik';

    public const BB_TB_OVERWEIGHT_RISK = 'Berisiko Gizi Lebih';

    public const BB_TB_OVERWEIGHT = 'Gizi Lebih';

    public const BB_TB_OBESE = 'Obesitas';

    // Legacy aliases retained while old reports are migrated.
    public const STATUS_NORMAL = 'Gizi Baik (Normal)';

    public const STATUS_UNDERWEIGHT = 'Gizi Kurang';

    public const STATUS_SEVERE_UNDERWEIGHT = 'Gizi Buruk';

    public const STATUS_OVERWEIGHT_RISK = 'Risiko Berat Lebih';

    protected $fillable = [
        'child_id',
        'weight',
        'height',
        'measurement_method',
        'age_days',
        'standardized_height',
        'bmi',
        'bb_u_zscore',
        'bb_u_status',
        'bb_u_flagged',
        'tb_u_zscore',
        'tb_u_status',
        'tb_u_flagged',
        'bb_tb_zscore',
        'bb_tb_status',
        'bb_tb_flagged',
        'imt_u_zscore',
        'imt_u_status',
        'imt_u_flagged',
        'calculation_version',
        'head_circumference',
        'arm_circumference',
        'vitamin_a',
        'deworming_medicine',
        'pmt_status',
        'status',
        'measurement_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'height' => 'float',
            'standardized_height' => 'float',
            'bmi' => 'float',
            'bb_u_zscore' => 'float',
            'bb_u_flagged' => 'boolean',
            'tb_u_zscore' => 'float',
            'tb_u_flagged' => 'boolean',
            'bb_tb_zscore' => 'float',
            'bb_tb_flagged' => 'boolean',
            'imt_u_zscore' => 'float',
            'imt_u_flagged' => 'boolean',
            'deworming_medicine' => 'boolean',
            'measurement_date' => 'date',
        ];
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function hasPriorityRisk(): bool
    {
        return $this->bb_u_flagged
            || $this->tb_u_flagged
            || $this->bb_tb_flagged
            || $this->imt_u_flagged
            || in_array($this->bb_u_status, [
                self::BB_U_SEVERELY_UNDERWEIGHT,
                self::BB_U_UNDERWEIGHT,
                self::BB_U_OVERWEIGHT_RISK,
            ], true)
            || in_array($this->tb_u_status, [self::TB_U_SEVERELY_STUNTED, self::TB_U_STUNTED], true)
            || in_array($this->bb_tb_status, [
                self::BB_TB_SEVERELY_WASTED,
                self::BB_TB_WASTED,
                self::BB_TB_OVERWEIGHT_RISK,
                self::BB_TB_OVERWEIGHT,
                self::BB_TB_OBESE,
            ], true)
            || in_array($this->imt_u_status, [
                self::BB_TB_OVERWEIGHT_RISK,
                self::BB_TB_OVERWEIGHT,
                self::BB_TB_OBESE,
            ], true);
    }

    public function priorityStatus(): ?string
    {
        foreach ([
            self::BB_TB_SEVERELY_WASTED,
            self::TB_U_SEVERELY_STUNTED,
            self::BB_U_SEVERELY_UNDERWEIGHT,
            self::BB_TB_OBESE,
            self::BB_TB_WASTED,
            self::TB_U_STUNTED,
            self::BB_U_UNDERWEIGHT,
            self::BB_TB_OVERWEIGHT,
            self::BB_TB_OVERWEIGHT_RISK,
            self::BB_U_OVERWEIGHT_RISK,
        ] as $status) {
            if (in_array($status, [
                $this->bb_tb_status,
                $this->imt_u_status,
                $this->tb_u_status,
                $this->bb_u_status,
            ], true)) {
                return $status;
            }
        }

        return $this->hasPriorityRisk() ? 'Perlu Verifikasi Data' : null;
    }

    public static function normalizeStatus(?string $status): ?string
    {
        if (blank($status)) {
            return null;
        }

        $normalized = mb_strtolower($status);

        if (str_contains($normalized, 'buruk') || str_contains($normalized, 'sangat kurang')) {
            return self::STATUS_SEVERE_UNDERWEIGHT;
        }

        if (str_contains($normalized, 'kurang')) {
            return self::STATUS_UNDERWEIGHT;
        }

        if (str_contains($normalized, 'lebih')) {
            return self::STATUS_OVERWEIGHT_RISK;
        }

        if (str_contains($normalized, 'baik') || str_contains($normalized, 'normal')) {
            return self::STATUS_NORMAL;
        }

        return null;
    }

    public static function isAtRisk(?string $status): bool
    {
        return in_array(self::normalizeStatus($status), [
            self::STATUS_UNDERWEIGHT,
            self::STATUS_SEVERE_UNDERWEIGHT,
        ], true);
    }
}
