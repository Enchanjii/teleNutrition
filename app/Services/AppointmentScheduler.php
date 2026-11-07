<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Carbon;

class AppointmentScheduler
{
    public const DURATION_MINUTES = 30;

    /** True if the dietitian or the patient already has a scheduled session overlapping this start time. */
    public function hasConflict(int $dietitianId, int $patientId, Carbon $start): bool
    {
        $windowStart = $start->copy()->subMinutes(self::DURATION_MINUTES);
        $windowEnd = $start->copy()->addMinutes(self::DURATION_MINUTES);

        return Appointment::where('status', 'scheduled')
            ->where('scheduled_at', '>', $windowStart)
            ->where('scheduled_at', '<', $windowEnd)
            ->where(function ($query) use ($dietitianId, $patientId) {
                $query->where('dietitian_id', $dietitianId)
                    ->orWhere('patient_id', $patientId);
            })
            ->exists();
    }
}