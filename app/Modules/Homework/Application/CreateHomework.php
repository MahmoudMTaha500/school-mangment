<?php

namespace App\Modules\Homework\Application;

use App\Models\User;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;

final class CreateHomework
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param array{teacher_id?:int|null,class_section_id:int,subject_id:int,title:string,body:string,due_at:string} $data */
    public function handle(int $userId, array $data): Homework
    {
        $user = User::query()->findOrFail($userId);
        $teacherId = $user->hasRole('school-admin')
            ? ($data['teacher_id'] ?? abort(422, 'Select an assigned teacher.'))
            : $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $data['class_section_id'], $data['subject_id']);
        unset($data['teacher_id']);

        return Homework::query()->create($data + ['teacher_id' => $teacherId]);
    }
}
