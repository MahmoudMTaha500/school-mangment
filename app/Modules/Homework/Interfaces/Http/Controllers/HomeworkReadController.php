<?php

namespace App\Modules\Homework\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Homework\Application\HomeworkReadAccess;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\Homework\Interfaces\Http\Resources\HomeworkResource;
use App\Modules\Homework\Interfaces\Http\Resources\SubmissionResource;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class HomeworkReadController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        $assignments = DB::table('teacher_class_subject')
            ->join('teachers', 'teachers.id', '=', 'teacher_class_subject.teacher_id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->join('class_sections', 'class_sections.id', '=', 'teacher_class_subject.class_section_id')
            ->join('subjects', 'subjects.id', '=', 'teacher_class_subject.subject_id')
            ->where('teachers.status', 'active')
            ->where('class_sections.status', 'active')
            ->where('subjects.status', 'active')
            ->when($request->user()->hasRole('teacher'), fn ($query) => $query->where('teachers.user_id', $request->user()->id))
            ->orderBy('users.name')
            ->orderBy('class_sections.grade')
            ->orderBy('class_sections.section')
            ->orderBy('subjects.name')
            ->get([
                'teachers.id as teacher_id',
                'users.name as teacher_name',
                'class_sections.id as class_section_id',
                'class_sections.grade',
                'class_sections.section',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
            ])->map(fn (object $assignment): array => [
                'teacher_id' => $assignment->teacher_id,
                'teacher_name' => $assignment->teacher_name,
                'class_section_id' => $assignment->class_section_id,
                'class_label' => "{$assignment->grade} - {$assignment->section}",
                'subject_id' => $assignment->subject_id,
                'subject_name' => $assignment->subject_name,
            ])->values();

        return response()->json(['data' => ['assignments' => $assignments]]);
    }

    public function index(Request $request, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $data = $request->validate(['student_id' => ['nullable', 'integer', 'exists:students,id'], 'class_section_id' => ['nullable', 'integer', 'exists:class_sections,id'], 'subject_id' => ['nullable', 'integer', 'exists:subjects,id'], 'status' => ['nullable', 'in:assigned,archived'], 'search' => ['nullable', 'string', 'max:100'], 'due_from' => ['nullable', 'date'], 'due_to' => ['nullable', 'date', 'after_or_equal:due_from'], 'sort' => ['nullable', 'in:due_at,title,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $query = Homework::query()->with('rubricCriteria')->withCount('submissions');

        if ($studentId = $data['student_id'] ?? null) {
            $student = Student::query()->findOrFail($studentId);
            $studentReadAccess->ensureCanViewStudent($request->user(), $student);
            $query->where('class_section_id', $student->class_section_id);
        } elseif ($request->user()->hasRole('school-admin')) {
            $query->when($data['class_section_id'] ?? null, fn ($builder, int $id) => $builder->where('class_section_id', $id));
        } elseif ($request->user()->hasRole('teacher')) {
            $query->where('teacher_id', Teacher::query()->where('user_id', $request->user()->id)->firstOrFail()->id)->when($data['class_section_id'] ?? null, fn ($builder, int $id) => $builder->where('class_section_id', $id));
        } else {
            abort(422, 'student_id is required for parent and student homework lists.');
        }

        $query->when($data['subject_id'] ?? null, fn ($builder, int $id) => $builder->where('subject_id', $id))->when($data['status'] ?? null, fn ($builder, string $status) => $builder->where('status', $status))->when($data['search'] ?? null, fn ($builder, string $search) => $builder->where(fn ($nested) => $nested->where('title', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%")))->when($data['due_from'] ?? null, fn ($builder, string $from) => $builder->where('due_at', '>=', $from))->when($data['due_to'] ?? null, fn ($builder, string $to) => $builder->where('due_at', '<=', $to));

        return HomeworkResource::collection($query->orderBy($data['sort'] ?? 'due_at', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function show(Request $request, Homework $homework, HomeworkReadAccess $access): JsonResponse
    {
        $access->ensureCanViewHomework($request->user(), $homework);

        return HomeworkResource::make($homework->load('rubricCriteria')->loadCount('submissions'))->response();
    }

    public function submissions(Request $request, Homework $homework, HomeworkReadAccess $access): JsonResponse
    {
        $access->ensureCanViewHomework($request->user(), $homework);
        abort_unless($request->user()->hasAnyRole(['school-admin', 'teacher']), 403);
        $data = $request->validate(['status' => ['nullable', 'in:submitted,late,graded'], 'student_id' => ['nullable', 'integer', 'exists:students,id'], 'sort' => ['nullable', 'in:submitted_at,grade,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return SubmissionResource::collection($homework->submissions()->with('rubricScores')->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['student_id'] ?? null, fn ($query, int $id) => $query->where('student_id', $id))->orderBy($data['sort'] ?? 'submitted_at', $data['direction'] ?? 'desc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showSubmission(Request $request, Submission $submission, HomeworkReadAccess $access): JsonResponse
    {
        $access->ensureCanViewSubmission($request->user(), $submission);

        return SubmissionResource::make($submission->load('rubricScores'))->response();
    }
}
