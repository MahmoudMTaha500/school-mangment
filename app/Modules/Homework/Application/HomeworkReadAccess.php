<?php

namespace App\Modules\Homework\Application;

use App\Models\User;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Staff\Application\TeacherClassAccess;

final class HomeworkReadAccess
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess, private readonly StudentReadAccess $studentReadAccess) {}

    public function ensureCanViewHomework(User $user, Homework $homework): void
    {
        if ($user->hasRole('school-admin')) {
            return;
        }
        if ($user->hasRole('student')) {
            abort_unless(Student::query()->where('user_id', $user->id)->where('class_section_id', $homework->class_section_id)->exists(), 403);

            return;
        }
        if ($user->hasRole('parent')) {
            $parent = ParentProfile::query()->where('user_id', $user->id)->firstOrFail();
            abort_unless($parent->students()->where('class_section_id', $homework->class_section_id)->exists(), 403);

            return;
        }
        $teacherId = $this->teacherClassAccess->teacherIdFor($user->id);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $homework->class_section_id, $homework->subject_id);
    }

    public function ensureCanViewSubmission(User $user, Submission $submission): void
    {
        $this->studentReadAccess->ensureCanViewStudent($user, Student::query()->findOrFail($submission->student_id));
    }
}
