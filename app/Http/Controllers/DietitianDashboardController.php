<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use App\Models\User;
use App\Services\ClinicalDecisionSupport;
use App\Services\IntakeSummary;
use App\Services\NutritionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DietitianDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        NutritionCalculator $calculator,
        IntakeSummary $summary,
        ClinicalDecisionSupport $cdss
    ): View {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $date = isset($validated['date']) ? Carbon::parse($validated['date']) : today();

        $patients = User::where('role', 'patient')->orderBy('name')->get();
        $profiles = PatientProfile::whereIn('user_id', $patients->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $rows = $patients->map(function (User $patient) use ($profiles, $calculator, $summary, $cdss, $date) {
            $profile = $profiles->get($patient->id);

            if (!$profile) {
                return ['patient' => $patient, 'has_profile' => false];
            }

            $targets = $calculator->targetsFor($profile);
            $intake = $summary->forDay($patient->id, $date);

            return [
                'patient' => $patient,
                'has_profile' => true,
                'targets' => $targets,
                'intake' => $intake,
                'flags' => $cdss->evaluate($targets, $intake),
            ];
        });

        return view('dietitian.dashboard', compact('rows', 'date'));
    }
}