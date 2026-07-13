<?php

namespace Tests\Unit\Wallet;

use App\Modules\Wallet\Infrastructure\Payments\StripeSignatureVerifier;
use PHPUnit\Framework\TestCase;

final class StripeSignatureVerifierTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    public function test_it_accepts_a_correctly_signed_recent_payload(): void
    {
        $payload = '{"id":"evt_1","type":"checkout.session.completed"}';
        $timestamp = time();

        $verifier = new StripeSignatureVerifier(self::SECRET);

        $this->assertTrue($verifier->isValid($payload, $this->sign($payload, $timestamp), $timestamp));
    }

    public function test_it_rejects_a_tampered_payload(): void
    {
        $timestamp = time();
        $header = $this->sign('{"id":"evt_1"}', $timestamp);

        $verifier = new StripeSignatureVerifier(self::SECRET);

        $this->assertFalse($verifier->isValid('{"id":"evt_tampered"}', $header, $timestamp));
    }

    public function test_it_rejects_a_replayed_old_signature(): void
    {
        $payload = '{"id":"evt_1"}';
        $oldTimestamp = time() - 3600;
        $header = $this->sign($payload, $oldTimestamp);

        $verifier = new StripeSignatureVerifier(self::SECRET, 300);

        // Signature is valid but the timestamp is outside tolerance.
        $this->assertFalse($verifier->isValid($payload, $header, time()));
    }

    public function test_it_rejects_a_missing_header(): void
    {
        $verifier = new StripeSignatureVerifier(self::SECRET);

        $this->assertFalse($verifier->isValid('{}', null));
    }

    private function sign(string $payload, int $timestamp): string
    {
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, self::SECRET);

        return "t={$timestamp},v1={$signature}";
    }
}
