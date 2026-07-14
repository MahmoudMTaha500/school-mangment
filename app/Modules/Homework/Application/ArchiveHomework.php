<?php

namespace App\Modules\Homework\Application;

use App\Models\User;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class ArchiveHomework
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    public function handle(int $userId, Homework $homework): void
    {
        $user = User::query()->findOrFail($userId);
        if (! $user->hasRole('school-admin')) {
            $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
            abort_unless($homework->teacher_id === $teacherId, 403, 'Only the assigned teacher can archive this homework.');
        }
        DB::transaction(function () use ($homework): void {
            if ($homework->status === 'archived') {
                return;
            }
            $homework->update(['status' => 'archived', 'archived_at' => now()]);
            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkArchived', 'payload' => json_encode(['homework_id' => $homework->id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);
        });
    }
}
