<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function slot(int $hour = 10, int $minute = 0): string
    {
        return now()->addDay()->setTime($hour, $minute)->format('Y-m-d\TH:i');
    }

    public function test_patient_can_book_an_appointment(): void
    {
        $patient = $this->user('patient');
        $dietitian = $this->user('dietitian');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => $this->slot(),
            ])
            ->assertRedirect(route('patient.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_overlapping_slot_with_same_dietitian_is_rejected(): void
    {
        $dietitian = $this->user('dietitian');

        $this->actingAs($this->user('patient'))
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => $this->slot(10, 0),
            ]);

        $this->actingAs($this->user('patient'))
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => $this->slot(10, 15),
            ])
            ->assertSessionHasErrors('scheduled_at');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_past_time_and_off_hours_are_rejected(): void
    {
        $patient = $this->user('patient');
        $dietitian = $this->user('dietitian');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => now()->subDay()->setTime(10, 0)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('scheduled_at');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => $this->slot(6, 0),
            ])
            ->assertSessionHasErrors('scheduled_at');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_patient_cannot_cancel_another_patients_appointment(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->user('patient')->id,
            'dietitian_id' => $this->user('dietitian')->id,
            'scheduled_at' => now()->addDay()->setTime(10, 0),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'room_id' => 'test-room-1',
        ]);

        $this->actingAs($this->user('patient'))
            ->patch(route('patient.appointments.cancel', $appointment))
            ->assertForbidden();

        $this->assertSame('scheduled', $appointment->fresh()->status);
    }

    public function test_patient_cannot_open_dietitian_appointments(): void
    {
        $this->actingAs($this->user('patient'))
            ->get(route('dietitian.appointments.index'))
            ->assertForbidden();
    }
}