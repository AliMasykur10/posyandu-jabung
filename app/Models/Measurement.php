<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    protected $fillable = ['child_id', 'weight', 'height', 'measurement_date', 'notes', 'status'];

    // Relasi: Setiap pengukuran milik satu anak
    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
