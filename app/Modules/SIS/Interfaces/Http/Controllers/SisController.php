<?php

namespace App\Modules\SIS\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Application\CreateParent;
use App\Modules\SIS\Application\CreateStudent;
use App\Modules\SIS\Application\CreateStudentAccount;
use App\Modules\SIS\Domain\Models\AcademicYear;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\SIS\Interfaces\Http\Requests\StoreAcademicYearRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreClassSectionRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreParentRequest;
use App\Modules\SIS\Interfaces\Http\Requests\StoreStudentRequest;
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
        return StudentResource::collection(Student::query()->with('classSection')->when($request->integer('class_section_id'), fn ($query, int $classSectionId) => $query->where('class_section_id', $classSectionId))->orderBy('last_name')->paginate(30))->response();
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
        $student->delete();

        return response()->json(status: 204);
    }

    public function createStudentAccount(Request $request, Student $student, CreateStudentAccount $createStudentAccount): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'string', 'min:12']]);

        return response()->json(['data' => $createStudentAccount->handle($student, $data)], 201);
    }

    public function parents(): JsonResponse
    {
        return ParentResource::collection(ParentProfile::query()->with(['user', 'students'])->paginate(30))->response();
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

    public function classSections(): JsonResponse
    {
        return ClassSectionResource::collection(ClassSection::query()->with('academicYear')->withCount('students')->orderBy('grade')->orderBy('section')->paginate(30))->response();
    }
}
