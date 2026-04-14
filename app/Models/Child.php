<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    protected $fillable = ['name', 
    'birth_date', 
    'gender', 
    'posyandu_id', 
    'parent_id',    
    'birth_weight' ];

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }
    // Tambahkan relasi ini agar bisa memanggil $child->parent->mother_name
    public function parent()
    {
        return $this->belongsTo(ParentDetail::class, 'parent_id');
    }
}
