<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DietitianSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('demo.dietitian_password') ?? (app()->isProduction() ? null : 'password');

        if ($password === null) {
            throw new RuntimeException('Set DIETITIAN_PASSWORD before seeding in production.');
        }

        User::updateOrCreate(
            ['email' => 'dietitian@example.com'],
            [
                'name' => 'Dr. Dietitian',
                'password' => Hash::make($password),
                'role' => 'dietitian',
            ]
        );
    }
}