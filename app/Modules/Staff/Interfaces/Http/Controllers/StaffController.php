<?php

namespace App\Modules\Staff\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Staff\Application\CreateTeacher;
use App\Modules\Staff\Domain\Models\Subject;
use App\Modules\Staff\Domain\Models\Teacher;
use App\Modules\Staff\Interfaces\Http\Requests\StoreSubjectRequest;
use App\Modules\Staff\Interfaces\Http\Requests\StoreTeacherRequest;
use App\Modules\Staff\Interfaces\Http\Resources\SubjectResource;
use App\Modules\Staff\Interfaces\Http\Resources\TeacherResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StaffController extends Controller
{
    public function teachers(): JsonResponse
    {
        return TeacherResource::collection(Teacher::query()->with('user')->orderBy('staff_no')->paginate(30))->response();
    }

    public function createTeacher(StoreTeacherRequest $request, CreateTeacher $createTeacher): JsonResponse
    {
        return TeacherResource::make($createTeacher->handle($request->validated())->load('user'))->response()->setStatusCode(201);
    }

    public function createSubject(StoreSubjectRequest $request): JsonResponse
    {
        return SubjectResource::make(Subject::query()->create($request->validated()))->response()->setStatusCode(201);
    }

    public function subjects(): JsonResponse
    {
        return SubjectResource::collection(Subject::query()->orderBy('name')->paginate(100))->response();
    }

    public function deleteSubject(Subject $subject): JsonResponse
    {
        $subject->delete();

        return response()->json(status: 204);
    }

    public function assignTeacher(Request $request): JsonResponse
    {
        $data = $request->validate(['teacher_id' => ['required', 'integer', 'exists:teachers,id'], 'class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'subject_id' => ['required', 'integer', 'exists:subjects,id']]);
        DB::table('teacher_class_subject')->insertOrIgnore($data + ['created_at' => now(), 'updated_at' => now()]);

        return response()->json(status: 204);
    }
}
