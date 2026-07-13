<?php

namespace App\Modules\Homework\Application;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Domain\Models\Student;

final class SubmitHomework
{
    public function handle(int $userId, Homework $homework, string $body): Submission
    {
        $student = Student::query()->where('user_id', $userId)->firstOrFail();
        abort_unless($student->class_section_id === $homework->class_section_id, 403, 'This homework is not assigned to you.');

        return Submission::query()->updateOrCreate(['homework_id' => $homework->id, 'student_id' => $student->id], ['body' => $body, 'submitted_at' => now()]);
    }
}
