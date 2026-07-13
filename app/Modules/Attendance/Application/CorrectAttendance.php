<?php

namespace App\Modules\Attendance\Application;

use App\Modules\Attendance\Domain\Models\AttendanceCorrection;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class CorrectAttendance
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function handle(int $userId, AttendanceRecord $attendance, string $status, string $reason): AttendanceRecord
    {
        return DB::transaction(function () use ($userId, $attendance, $status, $reason): AttendanceRecord {
            $attendance = AttendanceRecord::query()->lockForUpdate()->findOrFail($attendance->id);
            $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
            $this->teacherClassAccess->ensureCanTeach($teacherId, $attendance->class_section_id);
            if ($attendance->status === $status) {
                return $attendance;
            }

            AttendanceCorrection::query()->create(['attendance_id' => $attendance->id, 'previous_status' => $attendance->status, 'new_status' => $status, 'reason' => $reason, 'corrected_by' => $userId]);
            $attendance->update(['status' => $status, 'justification' => $reason, 'recorded_by' => $userId]);
            DB::table('outbox_messages')->insert(['event_type' => 'AttendanceCorrected', 'payload' => json_encode(['attendance_id' => $attendance->id, 'student_id' => $attendance->student_id, 'previous_status' => $attendance->getOriginal('status'), 'new_status' => $status], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $attendance->refresh();
        });
    }
}
