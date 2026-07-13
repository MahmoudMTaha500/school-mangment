<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

final class RefundTopupIntent
{
    public function __construct(private readonly ApplyWalletTransaction $applyWalletTransaction) {}

    public function handle(PaymentIntent $intent): PaymentIntent
    {
        return DB::transaction(function () use ($intent): PaymentIntent {
            $intent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);
            abort_unless($intent->status === 'succeeded', 422, 'Only a succeeded payment intent can be refunded.');
            $this->applyWalletTransaction->handle(['account_id' => $intent->wallet_account_id, 'type' => WalletTransaction::DEBIT, 'amount' => $intent->amount, 'idempotency_key' => "payment-refund:{$intent->id}", 'reference_type' => PaymentIntent::class, 'reference_id' => $intent->id]);
            $intent->update(['status' => 'refunded']);
            DB::table('outbox_messages')->insert(['event_type' => 'WalletTopupRefunded', 'payload' => json_encode(['payment_intent_id' => $intent->id, 'wallet_account_id' => $intent->wallet_account_id, 'amount' => $intent->amount], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $intent->refresh();
        });
    }
}
