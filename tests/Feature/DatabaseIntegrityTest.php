<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ClinicalNote;
use App\Models\Food;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function food(): Food
    {
        return Food::create([
            'name' => 'Test food',
            'serving_size' => 100,
            'serving_unit' => 'g',
            'calories' => 100,
            'protein_g' => 10,
            'carbs_g' => 10,
            'fat_g' => 1,
            'fiber_g' => 1,
            'sodium_mg' => 50,
        ]);
    }

    private function appointment(User $patient, User $dietitian, string $roomId): Appointment
    {
        return Appointment::create([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'scheduled_at' => now()->addDay()->setTime(10, 0),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'room_id' => $roomId,
        ]);
    }

    private function note(int $patientId, int $dietitianId): ClinicalNote
    {
        return ClinicalNote::create([
            'patient_id' => $patientId,
            'dietitian_id' => $dietitianId,
            'note_date' => today(),
            'assessment' => 'Assessment text',
            'diagnosis_term' => 'Excessive energy intake',
            'etiology' => 'Etiology text',
            'signs_symptoms' => 'Signs text',
            'intervention' => 'Intervention text',
            'monitoring_evaluation' => 'Monitoring text',
        ]);
    }

    public function test_food_used_in_a_meal_log_cannot_be_deleted(): void
    {
        $food = $this->food();

        MealLog::create([
            'user_id' => $this->user('patient')->id,
            'food_id' => $food->id,
            'quantity' => 1,
            'meal_type' => 'lunch',
            'logged_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        $food->delete();
    }

    public function test_deleting_a_patient_removes_their_dependent_records(): void
    {
        $patient = $this->user('patient');
        $dietitian = $this->user('dietitian');

        PatientProfile::create([
            'user_id' => $patient->id,
            'birth_date' => now()->subYears(30)->toDateString(),
            'sex' => 'female',
            'weight_kg' => 60,
            'height_cm' => 160,
            'activity_level' => 'light',
        ]);

        MealLog::create([
            'user_id' => $patient->id,
            'food_id' => $this->food()->id,
            'quantity' => 1,
            'meal_type' => 'breakfast',
            'logged_at' => now(),
        ]);

        $this->appointment($patient, $dietitian, 'room-cascade-1');

        $patient->delete();

        $this->assertDatabaseMissing('patient_profiles', ['user_id' => $patient->id]);
        $this->assertDatabaseMissing('meal_logs', ['user_id' => $patient->id]);
        $this->assertDatabaseMissing('appointments', ['patient_id' => $patient->id]);
    }

    public function test_clinical_note_must_reference_existing_users(): void
    {
        $this->expectException(QueryException::class);

        $this->note(999999, 999998);
    }

    public function test_dietitian_with_chart_notes_cannot_be_deleted(): void
    {
        $patient = $this->user('patient');
        $dietitian = $this->user('dietitian');

        $this->note($patient->id, $dietitian->id);

        $this->expectException(QueryException::class);

        $dietitian->delete();
    }

    public function test_room_ids_must_be_unique(): void
    {
        $patient = $this->user('patient');
        $dietitian = $this->user('dietitian');

        $this->appointment($patient, $dietitian, 'room-duplicate');

        $this->expectException(QueryException::class);

        $this->appointment($patient, $dietitian, 'room-duplicate');
    }
}