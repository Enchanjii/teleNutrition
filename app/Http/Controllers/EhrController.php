<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PatientProfile;
use App\Models\User;
use App\Services\FhirMapper;
use App\Services\IntakeSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EhrController extends Controller
{
    public function patient(Request $request, string $id, FhirMapper $fhir): JsonResponse
    {
        $user = $this->findPatient($id);

        if (!$user) {
            return $this->notFound();
        }

        $this->audit($request, 'ehr.patient.read', $user);

        $profile = PatientProfile::where('user_id', $user->id)->first();

        return $this->fhirResponse($fhir->patient($user, $profile));
    }

    public function observations(Request $request, FhirMapper $fhir, IntakeSummary $summary): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'patient' => ['required', 'uuid'],
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        if ($validator->fails()) {
            return $this->fhirResponse(FhirMapper::outcome('error', 'invalid', $validator->errors()->first()), 400);
        }

        $user = $this->findPatient((string) $request->query('patient'));

        if (!$user) {
            return $this->notFound();
        }

        $date = $request->query('date') ? Carbon::parse($request->query('date')) : today();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        $resources = $fhir->intake($user, $summary->forDay($user->id, $date), $date);

        if ($profile) {
            $resources = [...$fhir->vitals($user, $profile), ...$resources];
        }

        $this->audit($request, 'ehr.observation.read', $user);

        return $this->fhirResponse(FhirMapper::bundle($resources));
    }

    private function findPatient(string $publicId): ?User
    {
        if (!Str::isUuid($publicId)) {
            return null;
        }

        return User::where('role', 'patient')->where('public_id', $publicId)->first();
    }

    private function audit(Request $request, string $action, User $subject): void
    {
        AuditLog::create([
            'actor_id' => null,
            'action' => $action,
            'subject_id' => $subject->id,
            'ip_address' => $request->ip(),
        ]);
    }

    private function notFound(): JsonResponse
    {
        return $this->fhirResponse(FhirMapper::outcome('error', 'not-found', 'Patient not found.'), 404);
    }

    private function fhirResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, ['Content-Type' => FhirMapper::CONTENT_TYPE]);
    }
}