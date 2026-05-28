<?php

namespace Database\Seeders;

use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@sisko.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
