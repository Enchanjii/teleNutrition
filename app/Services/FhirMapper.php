<?php

namespace App\Services;

use App\Models\PatientProfile;
use App\Models\User;
use Carbon\CarbonInterface;

class FhirMapper
{
    public const CONTENT_TYPE = 'application/fhir+json';

    private const UCUM = 'http://unitsofmeasure.org';
    private const LOINC = 'http://loinc.org';
    private const CATEGORY_SYSTEM = 'http://terminology.hl7.org/CodeSystem/observation-category';

    // [display name, UCUM unit]. The codes are local; map them to a standard terminology if your course requires it.
    private const NUTRIENTS = [
        'calories' => ['Energy intake', 'kcal'],
        'protein_g' => ['Protein intake', 'g'],
        'carbs_g' => ['Carbohydrate intake', 'g'],
        'fat_g' => ['Fat intake', 'g'],
        'fiber_g' => ['Fiber intake', 'g'],
        'sodium_mg' => ['Sodium intake', 'mg'],
    ];

    public function __construct(private readonly Privacy $privacy)
    {
    }

    /** De-identified Patient: pseudonymous identifier, gender, and an age band. No name or birth date. */
    public function patient(User $user, ?PatientProfile $profile): array
    {
        $resource = [
            'resourceType' => 'Patient',
            'id' => $user->public_id,
            'identifier' => [
                [
                    'system' => 'urn:telenutrition:patient-code',
                    'value' => $this->privacy->pseudonym($user->public_id),
                ]
            ],
        ];

        if ($profile) {
            $resource['gender'] = $profile->sex;
            $resource['extension'] = [
                [
                    'url' => 'urn:telenutrition:age-band',
                    'valueString' => $this->privacy->ageBand($profile->birth_date->age),
                ]
            ];
        }

        return $resource;
    }

    /** Body weight and height Observations (LOINC 29463-7 and 8302-2). */
    public function vitals(User $user, PatientProfile $profile): array
    {
        $recorded = $profile->updated_at->toIso8601String();

        return [
            $this->quantityObservation(
                $user,
                'weight',
                'vital-signs',
                [self::LOINC, '29463-7', 'Body weight'],
                (float) $profile->weight_kg,
                'kg',
                $recorded
            ),
            $this->quantityObservation(
                $user,
                'height',
                'vital-signs',
                [self::LOINC, '8302-2', 'Body height'],
                (float) $profile->height_cm,
                'cm',
                $recorded
            ),
        ];
    }

    /** One Observation per nutrient for a day. Returns nothing when no meals were logged. */
    public function intake(User $user, array $intake, CarbonInterface $date): array
    {
        if (($intake['calories'] ?? 0) <= 0) {
            return [];
        }

        $day = $date->format('Y-m-d');
        $observations = [];

        foreach (self::NUTRIENTS as $key => [$display, $unit]) {
            $observations[] = $this->quantityObservation(
                $user,
                "intake-{$key}-{$day}",
                null,
                ['urn:telenutrition:nutrient', $key, $display],
                (float) $intake[$key],
                $unit,
                $day
            );
        }

        return $observations;
    }

    public static function bundle(array $resources): array
    {
        return [
            'resourceType' => 'Bundle',
            'type' => 'searchset',
            'total' => count($resources),
            'entry' => array_map(fn(array $resource) => ['resource' => $resource], array_values($resources)),
        ];
    }

    public static function outcome(string $severity, string $code, string $message): array
    {
        return [
            'resourceType' => 'OperationOutcome',
            'issue' => [['severity' => $severity, 'code' => $code, 'diagnostics' => $message]],
        ];
    }

    /** @param array{0: string, 1: string, 2: string} $coding system, code, display */
    private function quantityObservation(
        User $user,
        string $seed,
        ?string $category,
        array $coding,
        float $value,
        string $unit,
        string $effective
    ): array {
        [$system, $code, $display] = $coding;

        $observation = [
            'resourceType' => 'Observation',
            'id' => md5($user->public_id . $seed),
            'status' => 'final',
            'code' => [
                'coding' => [['system' => $system, 'code' => $code, 'display' => $display]],
                'text' => $display,
            ],
            'subject' => ['reference' => 'Patient/' . $user->public_id],
            'effectiveDateTime' => $effective,
            'valueQuantity' => ['value' => $value, 'unit' => $unit, 'system' => self::UCUM, 'code' => $unit],
        ];

        if ($category !== null) {
            $observation['category'] = [
                [
                    'coding' => [['system' => self::CATEGORY_SYSTEM, 'code' => $category]],
                ]
            ];
        }

        return $observation;
    }
}