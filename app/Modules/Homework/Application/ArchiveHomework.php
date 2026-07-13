<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class ArchiveHomework
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function handle(int $userId, Homework $homework): void
    {
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $homework->class_section_id, $homework->subject_id);
        DB::transaction(function () use ($homework): void {
            if ($homework->status === 'archived') {
                return;
            }
            $homework->update(['status' => 'archived', 'archived_at' => now()]);
            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkArchived', 'payload' => json_encode(['homework_id' => $homework->id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
        });
    }
}
