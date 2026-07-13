<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;

final class CreateHomework
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param array{class_section_id:int,subject_id:int,title:string,body:string,due_at:string} $data */
    public function handle(int $userId, array $data): Homework
    {
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $data['class_section_id'], $data['subject_id']);

        return Homework::query()->create($data + ['teacher_id' => $teacherId]);
    }
}
