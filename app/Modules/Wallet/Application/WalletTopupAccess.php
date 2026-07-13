<?php

namespace App\Modules\Wallet\Application;

use App\Models\User;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Domain\Models\WalletAccount;

final class WalletTopupAccess
{
    public function ensureCanTopup(User $user, WalletAccount $account): void
    {
        if ($user->hasRole('school-admin')) {
            return;
        }
        $parent = ParentProfile::query()->where('user_id', $user->id)->firstOrFail();
        if ($account->owner_type === ParentProfile::class && $account->owner_id === $parent->id) {
            return;
        }
        if ($account->owner_type === Student::class && $parent->students()->whereKey($account->owner_id)->exists()) {
            return;
        }
        abort(403, 'You can only top up your wallet or a linked child’s wallet.');
    }
}
