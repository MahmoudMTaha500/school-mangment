<?php

namespace App\Modules\Staff\Interfaces\Http\Resources;

use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Teacher */
final class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'staff_no' => $this->staff_no, 'user_id' => $this->user_id, 'name' => $this->whenLoaded('user', fn () => $this->user->name), 'email' => $this->whenLoaded('user', fn () => $this->user->email), 'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
