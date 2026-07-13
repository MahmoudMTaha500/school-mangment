<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use App\Modules\Wallet\Domain\Models\PaymentIntent;

final class ReconcileTopupIntents
{
    public function __construct(private readonly ConfirmTopupIntent $confirmTopupIntent, private readonly FailTopupIntent $failTopupIntent, private readonly PaymentGateway $paymentGateway) {}

    /** @return array{checked:int,succeeded:int,failed:int,pending:int} */
    public function handle(int $olderThanMinutes = 30, int $limit = 100): array
    {
        $result = ['checked' => 0, 'succeeded' => 0, 'failed' => 0, 'pending' => 0];
        PaymentIntent::query()->where('status', 'pending')->where('created_at', '<=', now()->subMinutes($olderThanMinutes))->orderBy('id')->limit($limit)->each(function (PaymentIntent $intent) use (&$result): void {
            $result['checked']++;
            if ($this->paymentGateway->isPaid($intent->gateway_payment_id)) {
                $this->confirmTopupIntent->handle($intent);
                $result['succeeded']++;

                return;
            }
            $this->failTopupIntent->handle($intent, 'Gateway reconciliation did not confirm payment.');
            $result['failed']++;
        });

        $result['pending'] = PaymentIntent::query()->where('status', 'pending')->count();

        return $result;
    }
}
