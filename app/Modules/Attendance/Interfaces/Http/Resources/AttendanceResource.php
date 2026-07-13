<?php

namespace App\Modules\Attendance\Interfaces\Http\Resources;

use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AttendanceRecord */
final class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'student_id' => $this->student_id, 'class_section_id' => $this->class_section_id, 'date' => $this->date, 'period' => $this->period, 'status' => $this->status, 'justification' => $this->justification, 'recorded_by' => $this->recorded_by, 'created_at' => $this->created_at?->toISOString()];
    }
}
