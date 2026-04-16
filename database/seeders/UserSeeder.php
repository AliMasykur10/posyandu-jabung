<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Jabung',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('1234'), // Password kamu nanti: password123
        ]);
    }
}
