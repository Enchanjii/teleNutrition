<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\JaasTokenGenerator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoSessionController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment, JaasTokenGenerator $tokens): View
    {
        $user = $request->user();

        abort_unless($appointment->isParticipant($user), 403);

        $appointment->loadMissing(['patient', 'dietitian']);

        $joinable = $appointment->isJoinable();

        $backUrl = $user->role === 'dietitian'
            ? route('dietitian.appointments.index')
            : route('patient.appointments.index');

        $session = null;
        if ($joinable) {
            $session = [
                'app_id' => config('jaas.app_id'),
                'room' => config('jaas.app_id') . '/' . $appointment->room_id,
                'jwt' => $tokens->forAppointment($appointment, $user),
                'display_name' => $user->name,
            ];
        }

        return view('appointments.session', compact('appointment', 'joinable', 'backUrl', 'session'));
    }
}