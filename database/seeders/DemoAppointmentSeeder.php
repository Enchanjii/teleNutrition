<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoAppointmentSeeder extends Seeder
{
    /** For local testing and demos only. Do not run in production. */
    public function run(): void
    {
        $patient = User::where('role', 'patient')->orderBy('id')->firstOrFail();
        $dietitian = User::where('role', 'dietitian')->orderBy('id')->firstOrFail();

        Appointment::create([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'scheduled_at' => now()->addMinutes(5),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'room_id' => 'telenutrition-' . Str::lower(Str::random(24)),
        ]);
    }
}