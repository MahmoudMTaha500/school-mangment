<?php

namespace App\Modules\Wallet\Application;

use App\Modules\Wallet\Domain\Models\WalletAccount;

final class ManageWalletAccount
{
    public function update(WalletAccount $account, array $data): WalletAccount
    {
        if (isset($data['currency'])) {
            abort_if($account->balance_cached !== 0, 422, 'Currency cannot change while the account has a balance.');
            $data['currency'] = strtoupper($data['currency']);
        }
        if (isset($data['status'])) {
            $data['archived_at'] = $data['status'] === 'archived' ? now() : null;
        }
        $account->update($data);

        return $account->refresh()->load('owner');
    }

    public function archive(WalletAccount $account): void
    {
        abort_if($account->balance_cached !== 0, 422, 'A wallet with a balance cannot be archived.');
        $account->update(['status' => 'archived', 'archived_at' => now()]);
    }
}
