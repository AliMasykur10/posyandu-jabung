<?php

namespace Database\Seeders;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        $this->storeUser([
            'name' => 'Admin Jabung Sisir',
            'email' => 'admin@gmail.com',
            'password' => $password,
            'role' => 'admin',
            'posyandu_id' => null,
        ]);

        $this->storeUser([
            'name' => 'Bidan Desa Jabung Sisir',
            'email' => 'bidan@posyandu.id',
            'password' => $password,
            'role' => 'bidan',
            'posyandu_id' => null,
        ]);

        Posyandu::orderBy('id')->each(function (Posyandu $posyandu) use ($password) {
            $unitName = preg_replace('/^Posyandu\s+/i', '', $posyandu->name);
            $slug = Str::slug($unitName, '.');

            $this->storeUser([
                'name' => 'Kader '.$unitName,
                'email' => 'kader.'.$slug.'@posyandu.id',
                'password' => $password,
                'role' => 'kader',
                'posyandu_id' => $posyandu->id,
            ]);
        });
    }

    private function storeUser(array $attributes): void
    {
        $user = User::updateOrCreate(
            ['email' => $attributes['email']],
            $attributes
        );

        $user->forceFill(['email_verified_at' => now()])->save();
    }
}
