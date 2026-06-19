<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    public const STATUS_NORMAL = 'Gizi Baik (Normal)';

    public const STATUS_UNDERWEIGHT = 'Gizi Kurang';

    public const STATUS_SEVERE_UNDERWEIGHT = 'Gizi Buruk';

    public const STATUS_OVERWEIGHT_RISK = 'Risiko Berat Lebih';

    protected $fillable = [
        'child_id',
        'weight',
        'height',
        'head_circumference',
        'arm_circumference',
        'vitamin_a',
        'deworming_medicine',
        'pmt_status',
        'status',
        'measurement_date',
        'notes'
    ];

    // Relasi: Setiap pengukuran milik satu anak
    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public static function normalizeStatus(?string $status): ?string
    {
        if (blank($status)) {
            return null;
        }

        $normalized = mb_strtolower($status);

        if (str_contains($normalized, 'buruk') || str_contains($normalized, 'sangat kurang') || str_contains($normalized, 'severely underweight')) {
            return self::STATUS_SEVERE_UNDERWEIGHT;
        }

        if (str_contains($normalized, 'kurang') || str_contains($normalized, 'underweight')) {
            return self::STATUS_UNDERWEIGHT;
        }

        if (str_contains($normalized, 'lebih') || str_contains($normalized, 'overweight')) {
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
