<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class ApplyWalletTransaction
{
    /** @param array{account_id:int,type:string,amount:int,idempotency_key:string,reference_type?:string,reference_id?:int} $data */
    public function handle(array $data): WalletTransaction
    {
        $existing = WalletTransaction::query()->where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data): WalletTransaction {
                $account = WalletAccount::query()->lockForUpdate()->findOrFail($data['account_id']);
                abort_unless($account->status === 'active', 422, 'Wallet account is archived.');
                $existing = WalletTransaction::query()->where('idempotency_key', $data['idempotency_key'])->first();
                if ($existing) {
                    return $existing;
                }

                $balanceAfter = $data['type'] === WalletTransaction::CREDIT ? $account->balance_cached + $data['amount'] : $account->balance_cached - $data['amount'];
                abort_if($balanceAfter < 0, 422, 'Insufficient wallet balance.');
                $account->update(['balance_cached' => $balanceAfter]);

                $transaction = WalletTransaction::query()->create($data + ['balance_after' => $balanceAfter]);
                DB::table('outbox_messages')->insert(['event_type' => $data['type'] === WalletTransaction::CREDIT ? 'WalletCredited' : 'WalletDebited', 'payload' => json_encode(['transaction_id' => $transaction->id, 'account_id' => $account->id, 'amount' => $data['amount'], 'balance_after' => $balanceAfter], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

                return $transaction;
            }, 3);
        } catch (QueryException $exception) {
            $existing = WalletTransaction::query()->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
            throw $exception;
        }
    }
}
