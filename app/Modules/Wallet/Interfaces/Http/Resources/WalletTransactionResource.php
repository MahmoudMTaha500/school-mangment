<?php

namespace App\Modules\Wallet\Interfaces\Http\Resources;

use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletTransaction */
final class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'account_id' => $this->account_id, 'type' => $this->type, 'amount_minor' => $this->amount, 'balance_after_minor' => $this->balance_after, 'reference_type' => $this->reference_type, 'reference_id' => $this->reference_id, 'created_at' => $this->created_at?->toISOString()];
    }
}
