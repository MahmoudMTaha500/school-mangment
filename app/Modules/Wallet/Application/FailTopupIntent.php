<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Domain\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;

final class FailTopupIntent
{
    public function handle(PaymentIntent $intent, string $reason): PaymentIntent
    {
        return DB::transaction(function () use ($intent, $reason): PaymentIntent {
            $intent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);
            abort_unless($intent->status === 'pending', 422, 'Only a pending payment intent can fail.');
            $intent->update(['status' => 'failed', 'metadata' => ($intent->metadata ?? []) + ['failure_reason' => $reason, 'failed_at' => now()->toISOString()]]);
            DB::table('outbox_messages')->insert(['event_type' => 'WalletTopupFailed', 'payload' => json_encode(['payment_intent_id' => $intent->id, 'wallet_account_id' => $intent->wallet_account_id, 'reason' => $reason], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $intent->refresh();
        });
    }
}
