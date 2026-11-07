<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DietitianSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dietitian@example.com'],
            [
                'name' => 'Dr. Dietitian',
                'password' => Hash::make('password'),
                'role' => 'dietitian',
            ]
        );
    }
}