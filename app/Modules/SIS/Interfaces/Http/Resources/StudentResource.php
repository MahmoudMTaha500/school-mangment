<?php

namespace App\Modules\SIS\Interfaces\Http\Resources;

use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Student */
final class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'code' => $this->code, 'first_name' => $this->first_name, 'last_name' => $this->last_name, 'full_name' => trim("{$this->first_name} {$this->last_name}"), 'date_of_birth' => $this->dob, 'enrollment_status' => $this->enrollment_status, 'class_section_id' => $this->class_section_id, 'has_login' => $this->user_id !== null, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(), 'class_section' => $this->whenLoaded('classSection', fn () => ['id' => $this->classSection->id, 'grade' => $this->classSection->grade, 'section' => $this->classSection->section])];
    }
}
