<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'father_name',
        'mother_name',
        'phone_number',
        'address'
    ];

    // Relasi ke anak (One to Many)
    public function children()
    {
        return $this->hasMany(Child::class, 'parent_id');
    }
}
