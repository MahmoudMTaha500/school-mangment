<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\Staff\Application\TeacherClassAccess;
use Illuminate\Support\Facades\DB;

final class GradeSubmission
{
    public function __construct(private readonly TeacherClassAccess $teacherClassAccess) {}

    /** @param list<array{criterion_id:int,score:int}>|null $rubricScores */
    public function handle(int $userId, Homework $homework, Submission $submission, int $grade, ?string $feedback, ?array $rubricScores = null): Submission
    {
        abort_unless($submission->homework_id === $homework->id, 404);
        $teacherId = $this->teacherClassAccess->teacherIdFor($userId);
        $this->teacherClassAccess->ensureCanTeach($teacherId, $homework->class_section_id, $homework->subject_id);

        return DB::transaction(function () use ($homework, $submission, $grade, $feedback, $userId, $rubricScores): Submission {
            $criteria = $homework->rubricCriteria()->get()->keyBy('id');
            if ($criteria->isNotEmpty()) {
                abort_if($rubricScores === null, 422, 'Rubric scores are required for this homework.');
                $scoresByCriterion = collect($rubricScores)->keyBy('criterion_id');
                abort_unless($scoresByCriterion->keys()->sort()->values()->all() === $criteria->keys()->sort()->values()->all(), 422, 'Provide one score for every rubric criterion.');
                foreach ($scoresByCriterion as $criterionId => $rubricScore) {
                    abort_if($rubricScore['score'] > $criteria[$criterionId]->max_score, 422, 'A rubric score exceeds its criterion maximum.');
                }
                abort_unless($grade === $scoresByCriterion->sum('score'), 422, 'The grade must equal the total rubric score.');
                $submission->rubricScores()->delete();
                $submission->rubricScores()->createMany($scoresByCriterion->values()->all());
            } else {
                abort_if($rubricScores !== null, 422, 'This homework has no rubric.');
            }
            $submission->update(['grade' => $grade, 'feedback' => $feedback, 'status' => 'graded', 'graded_by' => $userId, 'graded_at' => now()]);
            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkGraded', 'payload' => json_encode(['submission_id' => $submission->id, 'homework_id' => $submission->homework_id, 'student_id' => $submission->student_id, 'grade' => $grade], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $submission->refresh()->load('rubricScores');
        });
    }
}
