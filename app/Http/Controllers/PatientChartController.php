<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicalNoteRequest;
use App\Models\ClinicalNote;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Models\User;
use App\Services\ClinicalDecisionSupport;
use App\Services\IntakeSummary;
use App\Services\NutritionCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PatientChartController extends Controller
{
    public function show(
        Request $request,
        User $patient,
        NutritionCalculator $calculator,
        IntakeSummary $summary,
        ClinicalDecisionSupport $cdss
    ): View {
        abort_unless($patient->role === 'patient', 404);

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $date = isset($validated['date']) ? Carbon::parse($validated['date']) : today();

        $profile = PatientProfile::where('user_id', $patient->id)->first();
        $targets = $profile ? $calculator->targetsFor($profile) : null;
        $intake = $summary->forDay($patient->id, $date);

        $rows = $targets ? $summary->compare($targets, $intake) : [];
        $flags = $targets ? $cdss->evaluate($targets, $intake) : [];

        $suggestedDiagnosis = collect($flags)
            ->pluck('term')
            ->first(fn($term) => in_array($term, ClinicalNote::DIAGNOSES, true));

        $logs = MealLog::with('food')
            ->where('user_id', $patient->id)
            ->whereDate('logged_at', $date)
            ->orderBy('logged_at')
            ->get();

        $notes = ClinicalNote::with('dietitian')
            ->where('patient_id', $patient->id)
            ->latest('note_date')
            ->latest('id')
            ->get();

        return view('dietitian.patients.show', compact(
            'patient',
            'profile',
            'targets',
            'rows',
            'flags',
            'suggestedDiagnosis',
            'logs',
            'notes',
            'date'
        ));
    }

    public function store(StoreClinicalNoteRequest $request, User $patient): RedirectResponse
    {
        abort_unless($patient->role === 'patient', 404);

        ClinicalNote::create([
            ...$request->validated(),
            'patient_id' => $patient->id,
            'dietitian_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('dietitian.patients.show', ['patient' => $patient, 'date' => $request->validated('note_date')])
            ->with('status', 'Chart note saved.');
    }
}