<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentDetail extends Model
{
    use HasFactory;

    protected $table = 'parent_details';

    protected $fillable = [
        'user_id',
        'posyandu_id',
        'no_kk',
        'nik_mother',
        'nik_father',
        'mother_name',
        'father_name',
        'phone_number',
        'address',
        'rt',
        'rw'
    ];

    // Relasi ke Posyandu (Many to One): Mengetahui data keluarga ini terdaftar di posyandu mana
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'posyandu_id');
    }

    // Relasi ke anak (One to Many)
    public function children()
    {
        return $this->hasMany(Child::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
