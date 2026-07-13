<?php

namespace App\Modules\Homework\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Interfaces\Http\Resources\HomeworkResource;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HomeworkReadController extends Controller
{
    public function index(Request $request, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $data = $request->validate(['student_id' => ['required', 'integer', 'exists:students,id']]);
        $student = Student::query()->findOrFail($data['student_id']);
        $studentReadAccess->ensureCanViewStudent($request->user(), $student);

        return HomeworkResource::collection(Homework::query()->where('class_section_id', $student->class_section_id)->with('rubricCriteria')->withCount('submissions')->orderBy('due_at')->paginate(30))->response();
    }
}
