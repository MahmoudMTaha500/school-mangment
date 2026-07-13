<?php

namespace App\Modules\Homework\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Homework\Application\CreateHomework;
use App\Modules\Homework\Application\GradeSubmission;
use App\Modules\Homework\Application\SubmitHomework;
use App\Modules\Homework\Domain\Models\Homework;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\Homework\Interfaces\Http\Requests\StoreHomeworkRequest;
use App\Modules\Homework\Interfaces\Http\Resources\HomeworkResource;
use App\Modules\Homework\Interfaces\Http\Resources\SubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HomeworkController extends Controller
{
    public function store(StoreHomeworkRequest $request, CreateHomework $createHomework): JsonResponse
    {
        return HomeworkResource::make($createHomework->handle($request->user()->id, $request->validated()))->response()->setStatusCode(201);
    }

    public function submit(Request $request, Homework $homework, SubmitHomework $submitHomework): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string']]);

        return SubmissionResource::make($submitHomework->handle($request->user()->id, $homework, $data['body']))->response()->setStatusCode(201);
    }

    public function grade(Request $request, Homework $homework, Submission $submission, GradeSubmission $gradeSubmission): JsonResponse
    {
        $data = $request->validate(['grade' => ['required', 'integer', 'min:0', 'max:100'], 'feedback' => ['nullable', 'string', 'max:5000']]);

        return SubmissionResource::make($gradeSubmission->handle($request->user()->id, $homework, $submission, $data['grade'], $data['feedback'] ?? null))->response();
    }
}
