<?php

namespace App\Modules\Attendance\Interfaces\Http\Resources;

use App\Modules\Attendance\Domain\Models\AttendanceJustification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AttendanceJustification */
final class AttendanceJustificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'attendance_id' => $this->attendance_id, 'requested_by' => $this->requested_by, 'reason' => $this->reason, 'status' => $this->status, 'reviewed_by' => $this->reviewed_by, 'reviewed_at' => $this->reviewed_at?->toISOString(), 'reviewer_note' => $this->reviewer_note, 'created_at' => $this->created_at?->toISOString()];
    }
}
