<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClinicalNote;
use App\Models\Food;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoSeeder extends Seeder
{
    // Portions are scaled a little each day so the weekly chart is not flat. Index = days ago.
    private const QUANTITY_FACTORS = [1.0, 0.9, 1.05, 1.0, 0.95, 1.1, 1.0];

    /** Fictional patients and meals, for local testing and demos only. Safe to run again. */
    public function run(): void
    {
        $password = config('demo.password') ?? (app()->isProduction() ? null : 'password');

        if ($password === null) {
            throw new RuntimeException('Set DEMO_PASSWORD before running DemoSeeder in production.');
        }

        $this->call([FoodSeeder::class, DietitianSeeder::class]);

        $dietitian = User::where('email', 'dietitian@example.com')->firstOrFail();
        $foods = Food::pluck('id', 'name');
        $definitions = $this->definitions();

        $existing = User::whereIn('email', array_column($definitions, 'email'))->pluck('id');
        MealLog::whereIn('user_id', $existing)->delete();
        Appointment::whereIn('patient_id', $existing)->delete();
        ClinicalNote::whereIn('patient_id', $existing)->delete();

        $patients = [];

        foreach ($definitions as $definition) {
            $patient = User::updateOrCreate(
                ['email' => $definition['email']],
                ['name' => $definition['name'], 'password' => Hash::make($password), 'role' => 'patient']
            );

            $patients[$definition['key']] = $patient;

            $this->seedProfile($patient, $definition['profile']);

            if ($definition['profile'] !== null) {
                $this->seedMeals($patient, $definition, $foods);
            }
        }

        $this->appointment($patients['maria'], $dietitian, today()->subDay()->setTime(10, 0), 'completed');
        $this->appointment($patients['maria'], $dietitian, today()->addDay()->setTime(10, 0), 'scheduled');
        // Opens right away so the video session can be demonstrated. Run this seeder again before presenting.
        $this->appointment($patients['jun'], $dietitian, now()->addMinutes(5)->startOfMinute(), 'scheduled');

        $this->seedNote($patients['jun'], $dietitian);

        $this->command?->info('Demo data ready: ' . implode(', ', array_column($definitions, 'email')));
    }

    private function seedProfile(User $patient, ?array $profile): void
    {
        if ($profile === null) {
            PatientProfile::where('user_id', $patient->id)->delete();

            return;
        }

        PatientProfile::updateOrCreate(
            ['user_id' => $patient->id],
            [
                'birth_date' => now()->subYears($profile['age'])->subMonths(2)->toDateString(),
                'sex' => $profile['sex'],
                'weight_kg' => $profile['weight_kg'],
                'height_cm' => $profile['height_cm'],
                'activity_level' => $profile['activity_level'],
            ]
        );
    }

    private function seedMeals(User $patient, array $definition, Collection $foods): void
    {
        for ($daysAgo = 0; $daysAgo < 7; $daysAgo++) {
            if (in_array($daysAgo, $definition['skip'], true)) {
                continue;
            }

            $factor = self::QUANTITY_FACTORS[$daysAgo];

            foreach ($definition['meals'] as [$type, $hour, $minute, $items]) {
                $when = today()->subDays($daysAgo)->setTime($hour, $minute);

                if ($when->isFuture()) {
                    $when = now()->subMinute();
                }

                foreach ($items as [$foodName, $servings]) {
                    MealLog::create([
                        'user_id' => $patient->id,
                        'food_id' => $foods->get($foodName) ?? throw new RuntimeException("Food not found: {$foodName}"),
                        'quantity' => round($servings * $factor, 1),
                        'meal_type' => $type,
                        'logged_at' => $when,
                    ]);
                }
            }
        }
    }

    private function appointment(User $patient, User $dietitian, Carbon $when, string $status): void
    {
        Appointment::create([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'scheduled_at' => $when,
            'duration_minutes' => 30,
            'status' => $status,
            'room_id' => 'telenutrition-demo-' . Str::lower(Str::random(20)),
        ]);
    }

    private function seedNote(User $patient, User $dietitian): void
    {
        ClinicalNote::create([
            'patient_id' => $patient->id,
            'dietitian_id' => $dietitian->id,
            'note_date' => today()->subDay(),
            'assessment' => 'Logged meals show a high intake of processed bread and salty foods. Energy intake is close to estimated needs, but fiber is low.',
            'diagnosis_term' => 'Excessive mineral intake (sodium)',
            'etiology' => 'frequent consumption of processed bread and salty foods',
            'signs_symptoms' => 'sodium intake above the 2,000 mg daily limit in the logged meals',
            'intervention' => 'Advised swapping white bread for whole-grain options, adding vegetables to each meal, and reading sodium on food labels.',
            'monitoring_evaluation' => 'Review meal logs at the next consultation. Target sodium within 2,000 mg per day and fiber closer to the daily target.',
        ]);
    }

    /** Fictional people. Meals are chosen so the decision-support rules show different results. */
    private function definitions(): array
    {
        return [
            [
                'key' => 'maria',
                'name' => 'Maria Santos',
                'email' => 'maria.demo@example.com',
                'profile' => ['age' => 29, 'sex' => 'female', 'weight_kg' => 58, 'height_cm' => 160, 'activity_level' => 'moderate'],
                'skip' => [],
                'meals' => [
                    ['breakfast', 7, 30, [['Oatmeal, cooked', 3], ['Banana', 1.5], ['Milk, whole', 2], ['Egg, boiled', 1]]],
                    ['lunch', 12, 15, [['White rice, cooked', 3], ['Chicken breast, cooked', 1], ['Kangkong (water spinach), raw', 2], ['Mango, ripe', 1.5]]],
                    ['dinner', 18, 45, [['Brown rice, cooked', 2.5], ['Tilapia, cooked', 1], ['Malunggay leaves, raw', 1], ['Sweet potato (kamote), boiled', 1.5], ['Papaya', 1]]],
                ],
            ],
            [
                'key' => 'jun',
                'name' => 'Jun Reyes',
                'email' => 'jun.demo@example.com',
                'profile' => ['age' => 41, 'sex' => 'male', 'weight_kg' => 82, 'height_cm' => 172, 'activity_level' => 'light'],
                'skip' => [],
                'meals' => [
                    ['breakfast', 7, 15, [['White bread', 3], ['Egg, boiled', 2], ['Milk, whole', 1]]],
                    ['lunch', 12, 30, [['White rice, cooked', 3], ['Chicken breast, cooked', 1.5], ['Kangkong (water spinach), raw', 1]]],
                    ['dinner', 19, 0, [['White rice, cooked', 2.5], ['Tilapia, cooked', 1.5], ['Banana', 1]]],
                ],
            ],
            [
                'key' => 'ana',
                'name' => 'Ana Villanueva',
                'email' => 'ana.demo@example.com',
                'profile' => ['age' => 22, 'sex' => 'female', 'weight_kg' => 52, 'height_cm' => 158, 'activity_level' => 'sedentary'],
                'skip' => [2, 5],
                'meals' => [
                    ['breakfast', 8, 0, [['Banana', 1], ['Milk, whole', 1]]],
                    ['lunch', 12, 45, [['White rice, cooked', 2], ['Egg, boiled', 1], ['Kangkong (water spinach), raw', 1]]],
                    ['dinner', 18, 30, [['Brown rice, cooked', 1.5], ['Papaya', 1]]],
                ],
            ],
            [
                'key' => 'rico',
                'name' => 'Rico Dela Cruz',
                'email' => 'rico.demo@example.com',
                'profile' => null,
                'skip' => [],
                'meals' => [],
            ],
        ];
    }
}