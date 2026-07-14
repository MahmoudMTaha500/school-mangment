<?php

namespace App\Modules\SIS\Interfaces\Http\Resources;

use App\Modules\SIS\Domain\Models\ParentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ParentProfile */
final class ParentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'user_id' => $this->user_id, 'name' => $this->whenLoaded('user', fn () => $this->user->name), 'email' => $this->whenLoaded('user', fn () => $this->user->email), 'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'students' => StudentResource::collection($this->whenLoaded('students')), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
