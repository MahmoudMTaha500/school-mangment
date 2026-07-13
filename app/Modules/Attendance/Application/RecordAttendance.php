<?php

namespace App\Modules\Attendance\Application;

use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class RecordAttendance
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param array{class_section_id:int,date:string,period:int,records:array<int, array{student_id:int,status:string,justification?:string}>} $data */
    public function handle(int $userId, array $data): void
    {
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $data['class_section_id']);
        $studentIds = collect($data['records'])->pluck('student_id')->unique();
        abort_unless(Student::query()->where('class_section_id', $data['class_section_id'])->whereIn('id', $studentIds)->count() === $studentIds->count(), 422, 'Every student must belong to the selected class section.');

        DB::transaction(function () use ($data, $userId): void {
            foreach ($data['records'] as $record) {
                $attendance = AttendanceRecord::query()->updateOrCreate(
                    ['student_id' => $record['student_id'], 'date' => $data['date'], 'period' => $data['period']],
                    ['class_section_id' => $data['class_section_id'], 'status' => $record['status'], 'recorded_by' => $userId, 'justification' => $record['justification'] ?? null],
                );
                if ($attendance->status === 'absent') {
                    DB::table('outbox_messages')->insert(['event_type' => 'AttendanceAbsenceRecorded', 'payload' => json_encode(['attendance_id' => $attendance->id, 'student_id' => $attendance->student_id, 'date' => $attendance->date, 'period' => $attendance->period], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
    }
}
