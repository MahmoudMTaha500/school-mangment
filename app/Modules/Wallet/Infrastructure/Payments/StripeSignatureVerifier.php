<?php

namespace App\Modules\Wallet\Infrastructure\Payments;

/**
 * Verifies the `Stripe-Signature` header using the scheme documented at
 * https://stripe.com/docs/webhooks/signatures: the signed payload is
 * "{timestamp}.{raw body}", HMAC-SHA256 with the endpoint signing secret.
 *
 * Verification is self-contained (no SDK) and constant-time, and it rejects
 * signatures whose timestamp is outside the configured tolerance to defeat
 * replay of a previously captured, validly signed request.
 */
final class StripeSignatureVerifier
{
    public function __construct(private readonly string $signingSecret, private readonly int $toleranceSeconds = 300) {}

    public function isValid(string $payload, ?string $signatureHeader, ?int $now = null): bool
    {
        if ($this->signingSecret === '' || $signatureHeader === null || $signatureHeader === '') {
            return false;
        }

        $parsed = $this->parseHeader($signatureHeader);
        if ($parsed['timestamp'] === null || $parsed['signatures'] === []) {
            return false;
        }

        $now ??= time();
        if (abs($now - $parsed['timestamp']) > $this->toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $parsed['timestamp'].'.'.$payload, $this->signingSecret);
        foreach ($parsed['signatures'] as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{timestamp:int|null,signatures:list<string>} */
    private function parseHeader(string $header): array
    {
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) !== 2) {
                continue;
            }
            [$key, $value] = $pair;
            if ($key === 't' && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        return ['timestamp' => $timestamp, 'signatures' => $signatures];
    }
}
