<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
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
}
