<?php

namespace App\Modules\SIS\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MobileStudentController extends Controller
{
    public function attendanceSummary(Request $request, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $data = $request->validate(['student_id' => ['required', 'integer', 'exists:students,id'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $student = Student::query()->findOrFail($data['student_id']);
        $studentReadAccess->ensureCanViewStudent($request->user(), $student);
        $summary = AttendanceRecord::query()->select('status', DB::raw('COUNT(*) as total'))->where('student_id', $student->id)->when($data['from'] ?? null, fn ($query, string $from) => $query->whereDate('date', '>=', $from))->when($data['to'] ?? null, fn ($query, string $to) => $query->whereDate('date', '<=', $to))->groupBy('status')->get();

        return response()->json(['data' => $summary]);
    }

    public function homework(Request $request, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $data = $request->validate(['student_id' => ['required', 'integer', 'exists:students,id']]);
        $student = Student::query()->findOrFail($data['student_id']);
        $studentReadAccess->ensureCanViewStudent($request->user(), $student);
        $homework = Homework::query()->where('class_section_id', $student->class_section_id)->with(['submissions' => fn ($query) => $query->where('student_id', $student->id)])->orderBy('due_at')->paginate(30);

        return response()->json(['data' => $homework]);
    }
}
