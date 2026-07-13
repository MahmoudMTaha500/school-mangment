<?php

namespace App\Modules\SIS\Application;

use App\Models\User;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Staff\Application\TeacherClassAccess;

final class StudentReadAccess
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function ensureCanViewStudent(User $user, Student $student): void
    {
        if ($user->hasRole('school-admin')) {
            return;
        }
        if ($user->hasRole('student')) {
            abort_unless($student->user_id === $user->id, 403, 'You can only view your own records.');

            return;
        }
        if ($user->hasRole('parent')) {
            $parent = ParentProfile::query()->where('user_id', $user->id)->firstOrFail();
            abort_unless($parent->students()->whereKey($student->id)->exists(), 403, 'You can only view your children’s records.');

            return;
        }
        $teacherId = $this->teacherClassAccess->teacherIdFor($user->id);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $student->class_section_id);
    }
}
