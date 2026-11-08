<?php

namespace App\Http\Middleware;

use App\Services\FhirMapper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EhrApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('ehr.api_key');
        $provided = (string) $request->bearerToken();

        if ($expected === '' || !hash_equals($expected, $provided)) {
            return response()->json(
                FhirMapper::outcome('error', 'login', 'Missing or invalid API key.'),
                401,
                ['Content-Type' => FhirMapper::CONTENT_TYPE, 'WWW-Authenticate' => 'Bearer']
            );
        }

        return $next($request);
    }
}