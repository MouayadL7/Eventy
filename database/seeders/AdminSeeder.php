<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //bcrypt('SuperSecretPassword')
        User::create([
            'phone'          => '0935713737',
            'email'          => 'thesarahtlass@gmail.com',
            'password'       =>  bcrypt('713737'),
            'email_verified' => 1,
            'role_id' => Role::ROLE_ADMINISTRATOR,
        ]);
    }
}
