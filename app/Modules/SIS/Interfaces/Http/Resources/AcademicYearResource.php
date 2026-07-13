<?php

namespace App\Modules\SIS\Interfaces\Http\Resources;

use App\Modules\SIS\Domain\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AcademicYear */
final class AcademicYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'starts_on' => $this->starts_on, 'ends_on' => $this->ends_on, 'created_at' => $this->created_at?->toISOString()];
    }
}
