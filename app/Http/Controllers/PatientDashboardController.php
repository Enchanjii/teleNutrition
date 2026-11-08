<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MealLog;
use App\Models\PatientProfile;
use App\Services\AppointmentScheduler;
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

        $loggedDays = MealLog::where('user_id', $user->id)
            ->where('logged_at', '>=', today()->subDays(6))
            ->get(['logged_at'])
            ->map(fn(MealLog $log) => $log->logged_at->toDateString())
            ->unique();

        $week = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = today()->subDays($i);

            $week[] = [
                'label' => $day->format('D'),
                'date' => $day->format('M j'),
                'calories' => $targets ? $summary->forDay($user->id, $day)['calories'] : 0,
                'logged' => $loggedDays->contains($day->toDateString()),
            ];
        }

        $nextAppointment = Appointment::with('dietitian')
            ->where('patient_id', $user->id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now()->subMinutes(AppointmentScheduler::DURATION_MINUTES + Appointment::JOIN_GRACE_MINUTES))
            ->orderBy('scheduled_at')
            ->first();

        return view('patient.dashboard', [
            'targets' => $targets,
            'rows' => $rows,
            'week' => $week,
            'streakDays' => $loggedDays->count(),
            'nextAppointment' => $nextAppointment,
            'firstName' => explode(' ', trim($user->name))[0],
        ]);
    }
}