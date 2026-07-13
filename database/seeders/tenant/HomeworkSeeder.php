<?php

namespace Database\Seeders\Tenant;

use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Staff\Domain\Models\Subject;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Database\Seeder;

final class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        $homework = Homework::query()->firstOrCreate(['title' => 'Fractions practice'], ['class_section_id' => ClassSection::query()->firstOrFail()->id, 'subject_id' => Subject::query()->firstOrFail()->id, 'teacher_id' => Teacher::query()->firstOrFail()->id, 'body' => 'Complete exercises 1 to 10.', 'due_at' => now()->addWeek()]);
        Submission::query()->firstOrCreate(['homework_id' => $homework->id, 'student_id' => Student::query()->firstOrFail()->id], ['body' => 'My answers.', 'submitted_at' => now()]);
    }
}
