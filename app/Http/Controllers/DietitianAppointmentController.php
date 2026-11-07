<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DietitianAppointmentController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $scheduled = Appointment::with('patient')
            ->where('dietitian_id', $userId)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();

        $history = Appointment::with('patient')
            ->where('dietitian_id', $userId)
            ->where('status', '!=', 'scheduled')
            ->latest('scheduled_at')
            ->limit(20)
            ->get();

        return view('dietitian.appointments.index', compact('scheduled', 'history'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless((int) $appointment->dietitian_id === (int) $request->user()->id, 403);
        abort_unless($appointment->status === 'scheduled', 409);

        $validated = $request->validate([
            'status' => ['required', 'in:completed,cancelled'],
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('status', 'Appointment updated.');
    }
}