<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use App\Services\IntakeSummary;
use App\Services\NutritionCalculator;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    private const LABELS = [
        'calories' => ['Calories', 'kcal'],
        'protein_g' => ['Protein', 'g'],
        'carbs_g' => ['Carbohydrates', 'g'],
        'fat_g' => ['Fat', 'g'],
        'fiber_g' => ['Fiber', 'g'],
        'sodium_mg' => ['Sodium (limit)', 'mg'],
    ];

    public function __invoke(NutritionCalculator $calculator, IntakeSummary $summary): View
    {
        $user = auth()->user();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        $targets = $profile ? $calculator->targetsFor($profile) : null;
        $intake = $summary->forDay($user->id, today());

        $rows = [];
        if ($targets) {
            foreach (self::LABELS as $key => [$label, $unit]) {
                $rows[] = [
                    'label' => $label,
                    'unit' => $unit,
                    'intake' => $intake[$key],
                    'target' => $targets[$key],
                    'percent' => $targets[$key] > 0 ? round($intake[$key] / $targets[$key] * 100) : 0,
                ];
            }
        }

        return view('patient.dashboard', compact('targets', 'rows'));
    }
}