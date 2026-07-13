<?php

namespace Database\Seeders\Tenant;

use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\Wallet\Application\ApplyWalletTransaction;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Database\Seeder;

final class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $parent = ParentProfile::query()->firstOrFail();
        $account = WalletAccount::query()->firstOrCreate(['owner_type' => ParentProfile::class, 'owner_id' => $parent->id], ['currency' => 'USD', 'balance_cached' => 0]);
        app(ApplyWalletTransaction::class)->handle(['account_id' => $account->id, 'type' => WalletTransaction::CREDIT, 'amount' => 5000, 'idempotency_key' => 'seed-wallet-credit']);
    }
}
