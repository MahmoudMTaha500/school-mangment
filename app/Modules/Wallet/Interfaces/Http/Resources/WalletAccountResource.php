<?php

namespace App\Modules\Wallet\Interfaces\Http\Resources;

use App\Modules\Wallet\Domain\Models\WalletAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletAccount */
final class WalletAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'owner_type' => class_basename($this->owner_type), 'owner_id' => $this->owner_id, 'balance_minor' => $this->balance_cached, 'currency' => $this->currency, 'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
