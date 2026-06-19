<?php

namespace Database\Seeders;

use App\Models\Posyandu;
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
        User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name'        => 'Admin Jabung',
            'password'    => Hash::make('1234'),
            'role'        => 'admin',
            'posyandu_id' => null,
        ]);

        $kaderAccounts = [
            'Posyandu Melati' => ['name' => 'Kader Melati', 'email' => 'kader.melati@posyandu.id'],
            'Posyandu Mawar' => ['name' => 'Kader Mawar', 'email' => 'kader.mawar@posyandu.id'],
            'Posyandu Kenanga' => ['name' => 'Kader Kenanga', 'email' => 'kader.kenanga@posyandu.id'],
            'Posyandu Dahlia' => ['name' => 'Kader Dahlia', 'email' => 'kader.dahlia@posyandu.id'],
            'Posyandu Anggrek' => ['name' => 'Kader Anggrek', 'email' => 'kader.anggrek@posyandu.id'],
        ];

        foreach ($kaderAccounts as $posyanduName => $account) {
            $posyandu = Posyandu::where('name', $posyanduName)->first();

            if (!$posyandu) {
                continue;
            }

            User::updateOrCreate([
                'email' => $account['email'],
            ], [
                'name'        => $account['name'],
                'password'    => Hash::make('12345678'),
                'role'        => 'kader',
                'posyandu_id' => $posyandu->id,
            ]);
        }
    }
}
