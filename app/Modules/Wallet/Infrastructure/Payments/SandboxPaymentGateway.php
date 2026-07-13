<?php

namespace App\Modules\Wallet\Infrastructure\Payments;

use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use Illuminate\Support\Str;

final class SandboxPaymentGateway implements PaymentGateway
{
    public function createCheckout(int $amount, string $currency, string $reference): array
    {
        $paymentId = 'sandbox_'.Str::uuid();

        return ['payment_id' => $paymentId, 'checkout_url' => "https://sandbox.invalid/checkout/{$paymentId}"];
    }

    public function isPaid(string $paymentId): bool
    {
        return str_starts_with($paymentId, 'sandbox_');
    }
}
