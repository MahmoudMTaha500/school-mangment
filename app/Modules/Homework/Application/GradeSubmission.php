<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\Staff\Application\TeacherClassAccess;

final class GradeSubmission
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function handle(int $userId, Homework $homework, Submission $submission, int $grade, ?string $feedback): Submission
    {
        abort_unless($submission->homework_id === $homework->id, 404);
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $homework->class_section_id, $homework->subject_id);
        $submission->update(['grade' => $grade, 'feedback' => $feedback]);

        return $submission->refresh();
    }
}
