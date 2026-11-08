<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use App\Models\User;
use App\Services\IntakeSummary;
use App\Services\Privacy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class DeidentifiedExportController extends Controller
{
    public function __invoke(Request $request, IntakeSummary $summary, Privacy $privacy): Response
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $date = isset($validated['date']) ? Carbon::parse($validated['date']) : today();

        $patients = User::where('role', 'patient')->get();
        $profiles = PatientProfile::whereIn('user_id', $patients->pluck('id'))->get()->keyBy('user_id');

        $rows = [];
        foreach ($patients as $patient) {
            $profile = $profiles->get($patient->id);

            if (!$profile) {
                continue;
            }

            $rows[] = [
                $privacy->pseudonym($patient->public_id),
                $privacy->ageBand($profile->birth_date->age),
                $profile->sex,
                $date->format('Y-m-d'),
                ...array_values($summary->forDay($patient->id, $date)),
            ];
        }

        // Sort by code so row order cannot reveal who registered first.
        usort($rows, fn($a, $b) => strcmp($a[0], $b[0]));

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['patient_code', 'age_band', 'sex', 'date', ...IntakeSummary::NUTRIENTS], ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="deidentified-intake-' . $date->format('Y-m-d') . '.csv"',
        ]);
    }
}