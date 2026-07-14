<?php

namespace App\Modules\SIS\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Application\CreateParent;
use App\Modules\SIS\Application\CreateStudent;
use App\Modules\SIS\Application\CreateStudentAccount;
use App\Modules\SIS\Application\ManageClassSection;
use App\Modules\SIS\Application\ManageParent;
use App\Modules\SIS\Domain\Models\AcademicYear;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\SIS\Interfaces\Http\Requests\StoreAcademicYearRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreClassSectionRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreParentRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreStudentRequest;
use App\Modules\SIS\Interfaces\Http\Requests\UpdateClassSectionRequest;
use App\Modules\SIS\Interfaces\Http\Requests\UpdateParentRequest;
use App\Modules\SIS\Interfaces\Http\Requests\UpdateStudentRequest;
use App\Modules\SIS\Interfaces\Http\Resources\AcademicYearResource;
use App\Modules\SIS\Interfaces\Http\Resources\ClassSectionResource;
use App\Modules\SIS\Interfaces\Http\Resources\ParentResource;
use App\Modules\SIS\Interfaces\Http\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SisController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $data = $request->validate(['class_section_id' => ['nullable', 'integer', 'exists:class_sections,id'], 'status' => ['nullable', 'string', 'max:30'], 'search' => ['nullable', 'string', 'max:100'], 'sort' => ['nullable', 'in:first_name,last_name,code,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return StudentResource::collection(Student::query()->with('classSection')->when($data['class_section_id'] ?? null, fn ($query, int $classSectionId) => $query->where('class_section_id', $classSectionId))->when($data['status'] ?? null, fn ($query, string $status) => $query->where('enrollment_status', $status))->when($data['search'] ?? null, fn ($query, string $search) => $query->where(fn ($nested) => $nested->where('code', 'like', "%{$search}%")->orWhere('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")))->orderBy($data['sort'] ?? 'last_name', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showStudent(Student $student): JsonResponse
    {
        return StudentResource::make($student->load('classSection'))->response();
    }

    public function createAcademicYear(StoreAcademicYearRequest $request): JsonResponse
    {
        return AcademicYearResource::make(AcademicYear::query()->create($request->validated()))->response()->setStatusCode(201);
    }

    public function createClassSection(StoreClassSectionRequest $request): JsonResponse
    {
        return ClassSectionResource::make(ClassSection::query()->create($request->validated()))->response()->setStatusCode(201);
    }

    public function createStudent(StoreStudentRequest $request, CreateStudent $createStudent): JsonResponse
    {
        return StudentResource::make($createStudent->handle($request->validated()))->response()->setStatusCode(201);
    }

    public function updateStudent(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student->update($request->validated());

        return StudentResource::make($student->refresh())->response();
    }

    public function deleteStudent(Student $student): JsonResponse
    {
        $student->update(['enrollment_status' => 'archived']);

        return response()->json(status: 204);
    }

    public function createStudentAccount(Request $request, Student $student, CreateStudentAccount $createStudentAccount): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'min:12']]);

        return response()->json(['data' => $createStudentAccount->handle($student, $data)], 201);
    }

    public function parents(Request $request): JsonResponse
    {
        $data = $request->validate(['status' => ['nullable', 'in:active,archived'], 'search' => ['nullable', 'string', 'max:100'], 'sort' => ['nullable', 'in:id,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return ParentResource::collection(ParentProfile::query()->with(['user', 'students'])->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['search'] ?? null, fn ($query, string $search) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))->orderBy($data['sort'] ?? 'id', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showParent(ParentProfile $parent): JsonResponse
    {
        return ParentResource::make($parent->load(['user', 'students']))->response();
    }

    public function updateParent(UpdateParentRequest $request, ParentProfile $parent, ManageParent $manageParent): JsonResponse
    {
        return ParentResource::make($manageParent->update($parent, $request->validated()))->response();
    }

    public function archiveParent(ParentProfile $parent, ManageParent $manageParent): JsonResponse
    {
        $manageParent->archive($parent);

        return response()->json(status: 204);
    }

    public function createParent(StoreParentRequest $request, CreateParent $createParent): JsonResponse
    {
        return ParentResource::make($createParent->handle($request->validated())->load(['user', 'students']))->response()->setStatusCode(201);
    }

    public function linkParent(Request $request, ParentProfile $parent, Student $student): JsonResponse
    {
        $data = $request->validate(['relationship' => ['required', 'string', 'max:50'], 'is_primary' => ['boolean']]);
        $parent->students()->syncWithoutDetaching([$student->id => ['relationship' => $data['relationship'], 'is_primary' => $data['is_primary'] ?? false]]);

        return response()->json(status: 204);
    }

    public function classSections(Request $request): JsonResponse
    {
        $data = $request->validate(['academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'], 'status' => ['nullable', 'in:active,archived'], 'search' => ['nullable', 'string', 'max:100'], 'sort' => ['nullable', 'in:grade,section,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        return ClassSectionResource::collection(ClassSection::query()->with('academicYear')->withCount('students')->when($data['academic_year_id'] ?? null, fn ($query, int $id) => $query->where('academic_year_id', $id))->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['search'] ?? null, fn ($query, string $search) => $query->where(fn ($nested) => $nested->where('grade', 'like', "%{$search}%")->orWhere('section', 'like', "%{$search}%")))->orderBy($data['sort'] ?? 'grade', $data['direction'] ?? 'asc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showClassSection(ClassSection $classSection): JsonResponse
    {
        return ClassSectionResource::make($classSection->load('academicYear')->loadCount('students'))->response();
    }

    public function updateClassSection(UpdateClassSectionRequest $request, ClassSection $classSection, ManageClassSection $manageClassSection): JsonResponse
    {
        return ClassSectionResource::make($manageClassSection->update($classSection, $request->validated()))->response();
    }

    public function archiveClassSection(ClassSection $classSection, ManageClassSection $manageClassSection): JsonResponse
    {
        $manageClassSection->archive($classSection);

        return response()->json(status: 204);
    }
}
