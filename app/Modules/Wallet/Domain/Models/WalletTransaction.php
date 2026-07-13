<?php

namespace App\Modules\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WalletTransaction extends Model
{
    public const CREDIT = 'credit';

    public const DEBIT = 'debit';

    public $timestamps = false;

    protected $fillable = ['account_id', 'type', 'amount', 'balance_after', 'reference_type', 'reference_id', 'idempotency_key'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class);
    }
}
