<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\WalletAccount;

final class CreateTopupIntent
{
    public function __construct(private readonly PaymentGateway $paymentGateway) {}

    /** @return array{intent:PaymentIntent,checkout_url:string} */
    public function handle(WalletAccount $account, int $amount, string $idempotencyKey): array
    {
        $existing = PaymentIntent::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            abort_unless($existing->wallet_account_id === $account->id, 422, 'Idempotency key is already associated with another wallet account.');

            return ['intent' => $existing, 'checkout_url' => $existing->metadata['checkout_url'] ?? ''];
        }
        $checkout = $this->paymentGateway->createCheckout($amount, $account->currency, $idempotencyKey);
        $intent = PaymentIntent::query()->create(['wallet_account_id' => $account->id, 'gateway' => 'sandbox', 'gateway_payment_id' => $checkout['payment_id'], 'amount' => $amount, 'currency' => $account->currency, 'idempotency_key' => $idempotencyKey, 'metadata' => ['checkout_url' => $checkout['checkout_url']]]);

        return ['intent' => $intent, 'checkout_url' => $checkout['checkout_url']];
    }
}
