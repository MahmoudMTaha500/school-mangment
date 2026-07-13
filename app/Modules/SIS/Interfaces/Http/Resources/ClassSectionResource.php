<?php

namespace App\Modules\SIS\Interfaces\Http\Resources;

use App\Modules\SIS\Domain\Models\ClassSection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClassSection */
final class ClassSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'academic_year_id' => $this->academic_year_id, 'grade' => $this->grade, 'section' => $this->section, 'label' => "{$this->grade} - {$this->section}", 'homeroom_teacher_id' => $this->homeroom_teacher_id, 'students_count' => $this->whenCounted('students'), 'academic_year' => AcademicYearResource::make($this->whenLoaded('academicYear'))];
    }
}
