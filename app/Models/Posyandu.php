<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;

    // Pastikan ini menggunakan 'name' dan 'address'
    protected $fillable = ['name', 'address'];
}
