<?php

namespace App\Modules\Notifications\Interfaces\Http\Resources;

use App\Modules\Notifications\Domain\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NotificationPreference */
final class NotificationPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'event_type' => $this->event_type, 'channels' => $this->channels, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
