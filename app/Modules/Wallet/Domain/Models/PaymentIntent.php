<?php

namespace App\Modules\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentIntent extends Model
{
    protected $fillable = ['wallet_account_id', 'gateway', 'gateway_payment_id', 'status', 'amount', 'currency', 'idempotency_key', 'metadata', 'confirmed_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'confirmed_at' => 'datetime'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WalletAccount::class, 'wallet_account_id');
    }
}
