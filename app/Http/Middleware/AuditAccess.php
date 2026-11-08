<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAccess
{
    /** Record who accessed which patient's data. Only successful requests are logged. */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $response = $next($request);

        $actor = $request->user();

        if ($actor && $response->getStatusCode() < 400) {
            $subject = $request->route('patient');

            AuditLog::create([
                'actor_id' => $actor->id,
                'action' => $action,
                'subject_id' => $subject instanceof User ? $subject->id : null,
                'ip_address' => $request->ip(),
            ]);
        }

        return $response;
    }
}