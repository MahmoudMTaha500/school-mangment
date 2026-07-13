<?php

namespace App\Modules\Wallet\Application;

use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Domain\Models\WalletAccount;

final class CreateWalletAccount
{
    /** @param array{owner_type:string,owner_id:int,currency:string} $data */
    public function handle(array $data): WalletAccount
    {
        $ownerClass = match ($data['owner_type']) {
            'parent' => ParentProfile::class,
            'student' => Student::class,
        };
        $ownerClass::query()->findOrFail($data['owner_id']);

        return WalletAccount::query()->firstOrCreate(['owner_type' => $ownerClass, 'owner_id' => $data['owner_id']], ['currency' => $data['currency'], 'balance_cached' => 0]);
    }
}
