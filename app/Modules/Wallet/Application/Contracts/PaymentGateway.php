<?php

namespace App\Modules\Wallet\Application\Contracts;

interface PaymentGateway
{
    /** @return array{payment_id:string,checkout_url:string} */
    public function createCheckout(int $amount, string $currency, string $reference): array;

    public function isPaid(string $paymentId): bool;
}
