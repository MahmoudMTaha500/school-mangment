<?php

namespace App\Modules\Staff\Application;

use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Support\Facades\DB;

final class TeacherClassAccess
{
    public function teacherIdFor(int $userId): int
    {
        return Teacher::query()->where('user_id', $userId)->value('id') ?? abort(403, 'Only teachers can perform this action.');
    }

    public function ensureCanTeach(int $teacherId, int $classSectionId, ?int $subjectId = null): void
    {
        $classSection = ClassSection::query()->findOrFail($classSectionId);
        if ($classSection->homeroom_teacher_id === $teacherId && $subjectId === null) {
            return;
        }

        $assignment = DB::table('teacher_class_subject')->where('teacher_id', $teacherId)->where('class_section_id', $classSectionId);
        if ($subjectId !== null) {
            $assignment->where('subject_id', $subjectId);
        }
        abort_unless($assignment->exists(), 403, 'You are not assigned to this class and subject.');
    }
}
