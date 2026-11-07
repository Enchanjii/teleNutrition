<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use App\Services\IntakeSummary;
use App\Services\NutritionCalculator;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    public function __invoke(NutritionCalculator $calculator, IntakeSummary $summary): View
    {
        $user = auth()->user();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        $targets = $profile ? $calculator->targetsFor($profile) : null;
        $rows = $targets ? $summary->compare($targets, $summary->forDay($user->id, today())) : [];

        return view('patient.dashboard', compact('targets', 'rows'));
    }
}