<?php

namespace App\Modules\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class WalletAccount extends Model
{
    protected $fillable = ['owner_type', 'owner_id', 'balance_cached', 'currency'];

    protected function casts(): array
    {
        return ['balance_cached' => 'integer'];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
