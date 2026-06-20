<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionStandard extends Model
{
    public const WEIGHT_FOR_AGE = 'wfa';

    public const LENGTH_HEIGHT_FOR_AGE = 'lhfa';

    public const BMI_FOR_AGE = 'bfa';

    public const WEIGHT_FOR_LENGTH = 'wfl';

    public const WEIGHT_FOR_HEIGHT = 'wfh';

    protected $fillable = [
        'indicator',
        'gender',
        'reference_value',
        'l',
        'm',
        's',
        'source_version',
    ];

    protected function casts(): array
    {
        return [
            'reference_value' => 'float',
            'l' => 'float',
            'm' => 'float',
            's' => 'float',
        ];
    }
}
