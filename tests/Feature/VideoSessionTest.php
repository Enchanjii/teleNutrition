<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoSessionTest extends TestCase
{
    use RefreshDatabase;

    private function appointment(User $patient, User $dietitian, array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'scheduled_at' => now()->addHours(3),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'room_id' => 'telenutrition-test-room',
        ], $overrides));
    }

    public function test_non_participant_cannot_open_the_session(): void
    {
        $appointment = $this->appointment(
            User::factory()->create(['role' => 'patient']),
            User::factory()->create(['role' => 'dietitian'])
        );

        $this->actingAs(User::factory()->create(['role' => 'patient']))
            ->get(route('appointments.session', $appointment))
            ->assertForbidden();
    }

    public function test_participant_sees_when_the_session_opens(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $appointment = $this->appointment($patient, User::factory()->create(['role' => 'dietitian']));

        $this->actingAs($patient)
            ->get(route('appointments.session', $appointment))
            ->assertOk()
            ->assertSee('This session opens at');
    }

    public function test_cancelled_session_is_not_available(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $appointment = $this->appointment(
            $patient,
            User::factory()->create(['role' => 'dietitian']),
            ['status' => 'cancelled']
        );

        $this->actingAs($patient)
            ->get(route('appointments.session', $appointment))
            ->assertOk()
            ->assertSee('no longer available');
    }
}