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
        return ['id' => $this->id, 'name' => $this->name, 'created_at' => $this->created_at?->toISOString()];
    }
}
