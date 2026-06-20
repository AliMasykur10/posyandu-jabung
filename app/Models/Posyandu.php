<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;

    // Pastikan ini menggunakan 'name' dan 'address'
    protected $fillable = ['name', 'address'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function parents()
    {
        return $this->hasMany(ParentDetail::class);
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }
}
