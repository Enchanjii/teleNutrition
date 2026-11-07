<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Firebase\JWT\JWT;
use RuntimeException;

class JaasTokenGenerator
{
    /** Short-lived, RS256-signed token that lets one user into one appointment's room. */
    public function forAppointment(Appointment $appointment, User $user): string
    {
        $appId = config('jaas.app_id');
        $keyId = config('jaas.key_id');

        if (!$appId || !$keyId) {
            throw new RuntimeException('JaaS is not configured. Set JAAS_APP_ID and JAAS_KEY_ID in .env.');
        }

        $now = time();

        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'iat' => $now,
            'nbf' => $now - 10,
            'exp' => $appointment->joinClosesAt()->timestamp,
            'sub' => $appId,
            'room' => $appointment->room_id,
            'context' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'moderator' => $user->role === 'dietitian',
                ],
                'features' => [
                    'recording' => false,
                    'livestreaming' => false,
                    'transcription' => false,
                    'outbound-call' => false,
                ],
            ],
        ];

        return JWT::encode($payload, $this->privateKey(), 'RS256', $keyId);
    }

    private function privateKey(): string
    {
        $inline = config('jaas.private_key');

        if ($inline) {
            return str_replace('\n', "\n", $inline);
        }

        $path = base_path((string) config('jaas.private_key_path'));

        if (!is_readable($path)) {
            throw new RuntimeException('JaaS private key file not found. Check JAAS_PRIVATE_KEY_PATH.');
        }

        return (string) file_get_contents($path);
    }
}