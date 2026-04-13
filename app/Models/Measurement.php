<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    protected $fillable = [
        'child_id',
        'measurement_date',
        'weight',
        'height',
        'nutritional_status'
    ];

    // Relasi: Setiap pengukuran milik satu anak
    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
