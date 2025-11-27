<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'AlifAdmin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'), // jangan lupa hash password
        ]);


    }
}
