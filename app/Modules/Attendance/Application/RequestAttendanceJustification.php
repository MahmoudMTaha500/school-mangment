<?php

namespace App\Modules\Attendance\Application;

use App\Models\User;
use App\Modules\Attendance\Domain\Models\AttendanceJustification;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\SIS\Application\StudentReadAccess;
use Illuminate\Support\Facades\DB;

final class RequestAttendanceJustification
{
    public function __construct(private readonly StudentReadAccess $studentReadAccess) {}

    public function handle(User $user, AttendanceRecord $attendance, string $reason): AttendanceJustification
    {
        abort_unless($user->hasRole('parent'), 403, 'Only a parent can submit an absence justification.');
        $this->studentReadAccess->ensureCanViewStudent($user, $attendance->student);
        abort_unless($attendance->status === 'absent', 422, 'Only an absence can be justified.');

        return DB::transaction(function () use ($user, $attendance, $reason): AttendanceJustification {
            $justification = AttendanceJustification::query()->updateOrCreate(
                ['attendance_id' => $attendance->id],
                ['requested_by' => $user->id, 'reason' => $reason, 'status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null, 'reviewer_note' => null],
            );
            DB::table('outbox_messages')->insert(['event_type' => 'AttendanceJustificationRequested', 'payload' => json_encode(['attendance_id' => $attendance->id, 'student_id' => $attendance->student_id, 'justification_id' => $justification->id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $justification;
        });
    }
}
