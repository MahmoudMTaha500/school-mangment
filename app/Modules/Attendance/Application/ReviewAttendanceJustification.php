<?php

namespace App\Modules\Attendance\Application;

use App\Modules\Attendance\Domain\Models\AttendanceJustification;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class ReviewAttendanceJustification
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function handle(int $userId, AttendanceJustification $justification, string $status, ?string $reviewerNote): AttendanceJustification
    {
        return DB::transaction(function () use ($userId, $justification, $status, $reviewerNote): AttendanceJustification {
            $justification = AttendanceJustification::query()->with('attendance')->lockForUpdate()->findOrFail($justification->id);
            abort_if($justification->status !== 'pending', 422, 'This justification has already been reviewed.');
            $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
            $this->teacherClassAccess->ensureCanTeach($teacherId, $justification->attendance->class_section_id);
            $justification->update(['status' => $status, 'reviewed_by' => $userId, 'reviewed_at' => now(), 'reviewer_note' => $reviewerNote]);
            DB::table('outbox_messages')->insert(['event_type' => 'AttendanceJustificationReviewed', 'payload' => json_encode(['attendance_id' => $justification->attendance_id, 'student_id' => $justification->attendance->student_id, 'justification_id' => $justification->id, 'status' => $status], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $justification->refresh();
        });
    }
}
