<?php

namespace App\Modules\Homework\Application;

use App\Models\User;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class ReplaceHomeworkRubric
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param list<array{title:string,max_score:int}> $criteria */
    public function handle(int $userId, Homework $homework, array $criteria): Homework
    {
        abort_if($homework->status === 'archived', 422, 'Archived homework cannot be edited.');
        $user = User::query()->findOrFail($userId);
        if (! $user->hasRole('school-admin')) {
            $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
            abort_unless($homework->teacher_id === $teacherId, 403, 'Only the assigned teacher can change this rubric.');
        }
        abort_if(array_sum(array_column($criteria, 'max_score')) > 100, 422, 'Rubric criteria cannot total more than 100 points.');

        return DB::transaction(function () use ($homework, $criteria): Homework {
            abort_if($homework->submissions()->whereNotNull('grade')->exists(), 422, 'A rubric cannot be changed after grading starts.');
            $homework->rubricCriteria()->delete();
            $homework->rubricCriteria()->createMany(array_map(fn (array $criterion, int $index): array => $criterion + ['position' => $index + 1], $criteria, array_keys($criteria)));
            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkRubricUpdated', 'payload' => json_encode(['homework_id' => $homework->id], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $homework->load('rubricCriteria');
        });
    }
}
