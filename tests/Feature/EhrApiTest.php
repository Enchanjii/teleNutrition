<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EhrApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['ehr.api_key' => 'test-key']);
    }

    private function patientWithProfile(): User
    {
        $patient = User::factory()->create([
            'role' => 'patient',
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

        return $patient;
    }

    public function test_requests_without_a_valid_key_are_rejected(): void
    {
        $patient = $this->patientWithProfile();
        $url = '/api/ehr/Patient/' . $patient->public_id;

        $this->getJson($url)
            ->assertUnauthorized()
            ->assertJsonPath('resourceType', 'OperationOutcome');

        $this->withToken('wrong-key')->getJson($url)->assertUnauthorized();
    }

    public function test_every_request_is_rejected_when_no_key_is_configured(): void
    {
        config(['ehr.api_key' => null]);

        $this->withToken('anything')
            ->getJson('/api/ehr/Patient/' . (string) Str::uuid())
            ->assertUnauthorized();
    }

    public function test_patient_resource_is_deidentified(): void
    {
        $patient = $this->patientWithProfile();

        $response = $this->withToken('test-key')->getJson('/api/ehr/Patient/' . $patient->public_id);

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/fhir+json')
            ->assertJsonPath('resourceType', 'Patient')
            ->assertJsonPath('id', $patient->public_id)
            ->assertJsonPath('gender', 'male')
            ->assertJsonPath('extension.0.valueString', '30-39')
            ->assertJsonMissingPath('name')
            ->assertJsonMissingPath('birthDate');

        $this->assertStringNotContainsString('Juan', $response->getContent());
        $this->assertStringNotContainsString('juan.secret', $response->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ehr.patient.read',
            'actor_id' => null,
            'subject_id' => $patient->id,
        ]);
    }

    public function test_observation_bundle_contains_vitals_and_intake(): void
    {
        $patient = $this->patientWithProfile();

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

        $response = $this->withToken('test-key')
            ->getJson('/api/ehr/Observation?patient=' . $patient->public_id . '&date=' . today()->format('Y-m-d'));

        $response->assertOk()
            ->assertJsonPath('resourceType', 'Bundle')
            ->assertJsonPath('total', 8);

        $values = collect($response->json('entry'))->mapWithKeys(fn($entry) => [
            $entry['resource']['code']['coding'][0]['code'] => $entry['resource']['valueQuantity']['value'],
        ]);

        $this->assertEquals(70, $values['29463-7']);
        $this->assertEquals(175, $values['8302-2']);
        $this->assertEquals(200, $values['calories']);
        $this->assertEquals(20, $values['protein_g']);
        $this->assertEquals(100, $values['sodium_mg']);
    }

    public function test_unknown_patient_and_bad_input_are_handled(): void
    {
        $this->withToken('test-key')
            ->getJson('/api/ehr/Patient/' . (string) Str::uuid())
            ->assertNotFound()
            ->assertJsonPath('resourceType', 'OperationOutcome');

        $this->withToken('test-key')
            ->getJson('/api/ehr/Observation?patient=not-a-uuid')
            ->assertStatus(400);

        $patient = $this->patientWithProfile();

        $this->withToken('test-key')
            ->getJson('/api/ehr/Observation?patient=' . $patient->public_id . '&date=2999-01-01')
            ->assertStatus(400);
    }
}