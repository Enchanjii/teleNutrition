<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Services\JaasTokenGenerator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tests\TestCase;

class JaasTokenGeneratorTest extends TestCase
{
    public function test_token_is_signed_and_scoped_to_the_appointment(): void
    {
        $resource = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        if ($resource === false || !openssl_pkey_export($resource, $privatePem)) {
            $this->markTestSkipped('OpenSSL could not generate a key on this machine.');
        }

        $publicPem = openssl_pkey_get_details($resource)['key'];

        config([
            'jaas.app_id' => 'vpaas-magic-cookie-test',
            'jaas.key_id' => 'vpaas-magic-cookie-test/key1',
            'jaas.private_key' => $privatePem,
        ]);

        $appointment = new Appointment([
            'room_id' => 'telenutrition-abc',
            'scheduled_at' => now()->addMinutes(5),
            'duration_minutes' => 30,
        ]);

        $dietitian = new User(['name' => 'Dr. Test', 'email' => 'dr@example.com', 'role' => 'dietitian']);
        $dietitian->id = 7;

        $patient = new User(['name' => 'Pat Test', 'email' => 'pat@example.com', 'role' => 'patient']);
        $patient->id = 8;

        $generator = app(JaasTokenGenerator::class);

        $dietitianToken = $generator->forAppointment($appointment, $dietitian);
        $decoded = JWT::decode($dietitianToken, new Key($publicPem, 'RS256'));

        $this->assertSame('jitsi', $decoded->aud);
        $this->assertSame('vpaas-magic-cookie-test', $decoded->sub);
        $this->assertSame('telenutrition-abc', $decoded->room);
        $this->assertTrue($decoded->context->user->moderator);
        $this->assertGreaterThan(time(), $decoded->exp);

        $header = json_decode(base64_decode(strtr(explode('.', $dietitianToken)[0], '-_', '+/')), true);
        $this->assertSame('vpaas-magic-cookie-test/key1', $header['kid']);

        $patientToken = $generator->forAppointment($appointment, $patient);
        $this->assertFalse(JWT::decode($patientToken, new Key($publicPem, 'RS256'))->context->user->moderator);
    }
}