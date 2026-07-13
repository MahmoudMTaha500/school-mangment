<?php

namespace App\Modules\Attendance\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\Attendance\Interfaces\Http\Resources\AttendanceResource;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AttendanceReadController extends Controller
{
    public function index(Request $request, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $data = $request->validate(['student_id' => ['required', 'integer', 'exists:students,id'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $student = Student::query()->findOrFail($data['student_id']);
        $studentReadAccess->ensureCanViewStudent($request->user(), $student);

        return AttendanceResource::collection(AttendanceRecord::query()->where('student_id', $student->id)->when($data['from'] ?? null, fn ($query, string $from) => $query->whereDate('date', '>=', $from))->when($data['to'] ?? null, fn ($query, string $to) => $query->whereDate('date', '<=', $to))->latest('date')->paginate(50))->response();
    }
}
