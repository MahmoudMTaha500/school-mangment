<?php

namespace App\Modules\Staff\Interfaces\Http\Resources;

use App\Modules\Staff\Domain\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subject */
final class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
