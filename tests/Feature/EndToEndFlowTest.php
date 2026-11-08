<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Food;
use App\Models\User;
use App\Services\JaasTokenGenerator;
use Database\Seeders\FoodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_to_dietitian_flow_works_end_to_end(): void
    {
        $this->seed(FoodSeeder::class);
        config(['jaas.app_id' => 'vpaas-magic-cookie-test']);

        // Replace the real token generator so this test needs no JaaS key.
        $this->app->instance(JaasTokenGenerator::class, new class extends JaasTokenGenerator {
            public function forAppointment(Appointment $appointment, User $user): string
            {
                return 'test-token-for-' . $user->id;
            }
        });

        $dietitian = User::factory()->create(['role' => 'dietitian', 'name' => 'Dr. Flow']);

        // 1. A new user registers and is a patient.
        $this->post('/register', [
            'name' => 'Flow Patient',
            'email' => 'flow@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $patient = User::where('email', 'flow@example.com')->firstOrFail();
        $this->assertSame('patient', $patient->role);

        $this->actingAs($patient)
            ->get(route('dashboard'))
            ->assertRedirect(route('patient.dashboard'));

        // 2. The patient completes the health profile.
        $this->actingAs($patient)
            ->put(route('patient.profile.update'), [
                'birth_date' => now()->subYears(30)->toDateString(),
                'sex' => 'female',
                'weight_kg' => 60,
                'height_cm' => 165,
                'activity_level' => 'moderate',
            ])
            ->assertRedirect(route('patient.profile.edit'));

        // 3. The patient logs a meal and sees it on the dashboard (2 x 89 kcal).
        $banana = Food::where('name', 'Banana')->firstOrFail();

        $this->actingAs($patient)
            ->post(route('patient.meals.store'), [
                'food_id' => $banana->id,
                'quantity' => 2,
                'meal_type' => 'breakfast',
                'logged_at' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('patient.meals.index'));

        $this->assertDatabaseHas('meal_logs', ['user_id' => $patient->id, 'food_id' => $banana->id]);

        $this->actingAs($patient)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('178');

        // 4. The patient books a consultation.
        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'dietitian_id' => $dietitian->id,
                'scheduled_at' => now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('patient.appointments.index'));

        // 5. The dietitian sees the patient, the meal and the booking.
        $this->actingAs($dietitian)
            ->get(route('dietitian.dashboard'))
            ->assertOk()
            ->assertSee('Flow Patient');

        $this->actingAs($dietitian)
            ->get(route('dietitian.patients.show', $patient))
            ->assertOk()
            ->assertSee('Banana');

        $this->actingAs($dietitian)
            ->get(route('dietitian.appointments.index'))
            ->assertOk()
            ->assertSee('Flow Patient');

        // 6. The dietitian writes an ADIME chart note.
        $this->actingAs($dietitian)
            ->post(route('dietitian.patients.notes.store', $patient), [
                'note_date' => today()->toDateString(),
                'assessment' => 'Energy intake is below the estimated need.',
                'diagnosis_term' => 'Inadequate energy intake',
                'etiology' => 'skipped meals',
                'signs_symptoms' => 'intake below 75 percent of the target',
                'intervention' => 'Plan three regular meals and one snack.',
                'monitoring_evaluation' => 'Review meal logs next week.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Chart note saved.');

        $this->assertDatabaseHas('clinical_notes', [
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'diagnosis_term' => 'Inadequate energy intake',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $dietitian->id,
            'subject_id' => $patient->id,
            'action' => 'chart.view',
        ]);

        // 7. Both participants can open the video session. Anyone else cannot.
        $session = Appointment::create([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'scheduled_at' => now()->addMinutes(5),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'room_id' => 'telenutrition-e2e-room',
        ]);

        foreach ([$patient, $dietitian] as $participant) {
            $this->actingAs($participant)
                ->get(route('appointments.session', $session))
                ->assertOk()
                ->assertSee('jitsi-container')
                ->assertSee('test-token-for-' . $participant->id);
        }

        $this->actingAs(User::factory()->create(['role' => 'patient']))
            ->get(route('appointments.session', $session))
            ->assertForbidden();
    }
}