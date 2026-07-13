<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Domain\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;

final class CancelTopupIntent
{
    public function handle(PaymentIntent $intent): PaymentIntent
    {
        return DB::transaction(function () use ($intent): PaymentIntent {
            $intent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);
            abort_unless($intent->status === 'pending', 422, 'Only a pending payment intent can be cancelled.');
            $intent->update(['status' => 'cancelled']);
            DB::table('outbox_messages')->insert(['event_type' => 'WalletTopupCancelled', 'payload' => json_encode(['payment_intent_id' => $intent->id, 'wallet_account_id' => $intent->wallet_account_id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $intent->refresh();
        });
    }
}
