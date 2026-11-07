<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $dietitians = User::where('role', 'dietitian')->orderBy('name')->get();

        $scheduled = Appointment::with('dietitian')
            ->where('patient_id', $userId)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();

        $history = Appointment::with('dietitian')
            ->where('patient_id', $userId)
            ->where('status', '!=', 'scheduled')
            ->latest('scheduled_at')
            ->limit(20)
            ->get();

        return view('patient.appointments.index', compact('dietitians', 'scheduled', 'history'));
    }

    public function store(StoreAppointmentRequest $request, AppointmentScheduler $scheduler): RedirectResponse
    {
        $start = Carbon::createFromFormat('Y-m-d\TH:i', $request->validated('scheduled_at'));
        $dietitianId = (int) $request->validated('dietitian_id');
        $patientId = $request->user()->id;

        if ($scheduler->hasConflict($dietitianId, $patientId, $start)) {
            return back()->withInput()->withErrors([
                'scheduled_at' => 'That time overlaps another appointment. Please choose a different time.',
            ]);
        }

        Appointment::create([
            'patient_id' => $patientId,
            'dietitian_id' => $dietitianId,
            'scheduled_at' => $start,
            'duration_minutes' => AppointmentScheduler::DURATION_MINUTES,
            'status' => 'scheduled',
            'room_id' => 'telenutrition-' . Str::lower(Str::random(24)),
        ]);

        return redirect()->route('patient.appointments.index')->with('status', 'Appointment booked.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->patient_id === (int) auth()->id(), 403);
        abort_unless($appointment->status === 'scheduled', 409);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('status', 'Appointment cancelled.');
    }
}