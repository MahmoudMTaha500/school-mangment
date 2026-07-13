<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Support\Facades\DB;

final class SubmitHomework
{
    public function handle(int $userId, Homework $homework, string $body): Submission
    {
        $student = Student::query()->where('user_id', $userId)->firstOrFail();
        abort_unless($student->class_section_id === $homework->class_section_id, 403, 'This homework is not assigned to you.');

        abort_if($homework->status === 'archived', 422, 'This homework is archived.');

        return DB::transaction(function () use ($homework, $student, $body): Submission {
            $submittedAt = now();
            $submission = Submission::query()->updateOrCreate(
                ['homework_id' => $homework->id, 'student_id' => $student->id],
                ['body' => $body, 'submitted_at' => $submittedAt, 'status' => $submittedAt->isAfter($homework->due_at) ? 'late' : 'submitted'],
            );

            DB::table('outbox_messages')->insert(['event_type' => 'HomeworkSubmitted', 'payload' => json_encode(['homework_id' => $homework->id, 'submission_id' => $submission->id, 'student_id' => $student->id, 'status' => $submission->status], JSON_THROW_ON_ERROR), 'available_at' => now(), 'attempts' => 0, 'created_at' => now(), 'updated_at' => now()]);

            return $submission;
        });
    }
}
