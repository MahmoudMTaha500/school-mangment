<?php

namespace App\Modules\Staff\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Staff\Application\CreateTeacher;
use App\Modules\Staff\Application\ManageSubject;
use App\Modules\Staff\Application\ManageTeacher;
use App\Modules\Staff\Domain\Models\Subject;
use App\Modules\Staff\Domain\Models\Teacher;
use App\Modules\Staff\Interfaces\Http\Requests\StoreSubjectRequest;
use App\Modules\Staff\Interfaces\Http\Requests\StoreTeacherRequest;
use App\Modules\Staff\Interfaces\Http\Requests\UpdateSubjectRequest;
use App\Modules\Staff\Interfaces\Http\Requests\UpdateTeacherRequest;
use App\Modules\Staff\Interfaces\Http\Resources\SubjectResource;
use App\Modules\Staff\Interfaces\Http\Resources\TeacherResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class StaffController extends Controller
{
    public function teachers(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => ['nullable', 'in:active,archived'], 'search' => ['nullable', 'string', 'max:100'], 'sort' => ['nullable', 'in:staff_no,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return TeacherResource::collection(Teacher::query()->with('user')->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['search'] ?? null, fn ($query, string $search) => $query->where(fn ($nested) => $nested->where('staff_no', 'like', "%{$search}%")->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))->orderBy($data['sort'] ?? 'staff_no', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showTeacher(Teacher $teacher): JsonResponse
    {
        return TeacherResource::make($teacher->load('user'))->response();
    }

    public function updateTeacher(UpdateTeacherRequest $request, Teacher $teacher, ManageTeacher $manageTeacher): JsonResponse
    {
        return TeacherResource::make($manageTeacher->update($teacher, $request->validated()))->response();
    }

    public function archiveTeacher(Teacher $teacher, ManageTeacher $manageTeacher): JsonResponse
    {
        $manageTeacher->archive($teacher);

        return response()->json(status: 204);
    }

    public function createTeacher(StoreTeacherRequest $request, CreateTeacher $createTeacher): JsonResponse
    {
        return TeacherResource::make($createTeacher->handle($request->validated())->load('user'))->response()->setStatusCode(201);
    }

    public function createSubject(StoreSubjectRequest $request): JsonResponse
    {
        return SubjectResource::make(Subject::query()->create($request->validated()))->response()->setStatusCode(201);
    }

    public function subjects(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => ['nullable', 'in:active,archived'], 'search' => ['nullable', 'string', 'max:100'], 'sort' => ['nullable', 'in:name,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return SubjectResource::collection(Subject::query()->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))->orderBy($data['sort'] ?? 'name', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 100)->withQueryString())->response();
    }

    public function showSubject(Subject $subject): JsonResponse
    {
        return SubjectResource::make($subject)->response();
    }

    public function updateSubject(UpdateSubjectRequest $request, Subject $subject, ManageSubject $manageSubject): JsonResponse
    {
        return SubjectResource::make($manageSubject->update($subject, $request->validated()))->response();
    }

    public function deleteSubject(Subject $subject, ManageSubject $manageSubject): JsonResponse
    {
        $manageSubject->archive($subject);

        return response()->json(status: 204);
    }

    public function assignTeacher(Request $request): JsonResponse
    {
        $data = $request->validate(['teacher_id' => ['required', 'integer', 'exists:teachers,id'], 'class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'subject_id' => ['required', 'integer', 'exists:subjects,id']]);
        DB::table('teacher_class_subject')->insertOrIgnore($data + ['created_at' => now(), 'updated_at' => now()]);

        return response()->json(status: 204);
    }
}
