<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    protected $fillable = ['name', 'birth_date', 'gender', 'posyandu_id'];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }
}
