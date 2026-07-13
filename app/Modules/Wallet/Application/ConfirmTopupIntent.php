<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

final class ConfirmTopupIntent
{
    public function __construct(private readonly ApplyWalletTransaction $applyWalletTransaction, private readonly PaymentGateway $paymentGateway) {}

    public function handle(PaymentIntent $intent): PaymentIntent
    {
        return DB::transaction(function () use ($intent): PaymentIntent {
            $intent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);
            if ($intent->status === 'succeeded') {
                return $intent;
            }
            abort_if(in_array($intent->status, ['cancelled', 'failed', 'refunded'], true), 422, 'This payment intent can no longer be confirmed.');
            abort_unless($this->paymentGateway->isPaid($intent->gateway_payment_id), 422, 'Payment has not been confirmed by the gateway.');
            $this->applyWalletTransaction->handle(['account_id' => $intent->wallet_account_id, 'type' => WalletTransaction::CREDIT, 'amount' => $intent->amount, 'idempotency_key' => "payment-intent:{$intent->id}", 'reference_type' => PaymentIntent::class, 'reference_id' => $intent->id]);
            $intent->update(['status' => 'succeeded', 'confirmed_at' => now()]);

            return $intent->refresh();
        });
    }
}
