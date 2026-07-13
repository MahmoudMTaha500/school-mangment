<?php

namespace App\Modules\Reporting\Application;

use Illuminate\Support\Facades\DB;

final class BuildHomeworkSummary
{
    /** @return array<int, array{id:int,title:string,due_at:string,students:int,submitted:int,graded:int}> */
    public function handle(int $classSectionId): array
    {
        return DB::table('homework')->leftJoin('submissions', 'submissions.homework_id', '=', 'homework.id')->selectRaw('homework.id, homework.title, homework.due_at, (SELECT COUNT(*) FROM students WHERE students.class_section_id = homework.class_section_id) as students, COUNT(submissions.id) as submitted, SUM(CASE WHEN submissions.grade IS NOT NULL THEN 1 ELSE 0 END) as graded')->where('homework.class_section_id', $classSectionId)->groupBy('homework.id', 'homework.title', 'homework.due_at', 'homework.class_section_id')->orderByDesc('homework.due_at')->get()->map(fn ($row) => ['id' => (int) $row->id, 'title' => $row->title, 'due_at' => $row->due_at, 'students' => (int) $row->students, 'submitted' => (int) $row->submitted, 'graded' => (int) $row->graded])->all();
    }
}
