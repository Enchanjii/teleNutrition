<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyControlsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => $role], $attributes));
    }

    public function test_every_user_gets_a_public_id(): void
    {
        $this->assertNotEmpty($this->user('patient')->public_id);
    }

    public function test_viewing_a_chart_is_recorded_in_the_audit_log(): void
    {
        $dietitian = $this->user('dietitian');
        $patient = $this->user('patient');

        $this->actingAs($dietitian)
            ->get(route('dietitian.patients.show', $patient))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $dietitian->id,
            'subject_id' => $patient->id,
            'action' => 'chart.view',
        ]);
    }

    public function test_forbidden_access_is_not_logged_as_a_view(): void
    {
        $patient = $this->user('patient');

        $this->actingAs($this->user('patient'))
            ->get(route('dietitian.patients.show', $patient))
            ->assertForbidden();

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_export_contains_no_direct_identifiers(): void
    {
        $patient = $this->user('patient', [
            'name' => 'Juan Secret Cruz',
            'email' => 'juan.secret@example.com',
        ]);

        PatientProfile::create([
            'user_id' => $patient->id,
            'birth_date' => now()->subYears(34)->toDateString(),
            'sex' => 'male',
            'weight_kg' => 70,
            'height_cm' => 175,
            'activity_level' => 'moderate',
        ]);

        $food = Food::create([
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

        MealLog::create([
            'user_id' => $patient->id,
            'food_id' => $food->id,
            'quantity' => 2,
            'meal_type' => 'lunch',
            'logged_at' => now(),
        ]);

        $response = $this->actingAs($this->user('dietitian'))->get(route('dietitian.export'));

        $response->assertOk();
        $csv = $response->getContent();

        $this->assertStringContainsString('PT-', $csv);
        $this->assertStringContainsString('30-39', $csv);
        $this->assertStringContainsString(',200,20,20,2,2,100', $csv);
        $this->assertStringNotContainsString('Juan', $csv);
        $this->assertStringNotContainsString('juan.secret', $csv);

        $this->assertDatabaseHas('audit_logs', ['action' => 'export.deidentified']);
    }

    public function test_patient_cannot_download_the_export(): void
    {
        $this->actingAs($this->user('patient'))
            ->get(route('dietitian.export'))
            ->assertForbidden();
    }
}