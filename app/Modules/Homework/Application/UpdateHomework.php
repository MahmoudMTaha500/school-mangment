<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class UpdateHomework
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param array{class_section_id?:int,subject_id?:int,title?:string,body?:string,due_at?:string} $data */
    public function handle(int $userId, Homework $homework, array $data): Homework
    {
        abort_if($homework->status === 'archived', 422, 'Archived homework cannot be edited.');
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $data['class_section_id'] ?? $homework->class_section_id, $data['subject_id'] ?? $homework->subject_id);

        return DB::transaction(function () use ($homework, $data): Homework {
            $homework->update($data);
            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkUpdated', 'payload' => json_encode(['homework_id' => $homework->id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $homework->refresh();
        });
    }
}
