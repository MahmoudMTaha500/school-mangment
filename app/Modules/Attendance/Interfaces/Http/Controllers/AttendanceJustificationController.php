<?php

namespace App\Modules\Attendance\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Application\RequestAttendanceJustification;
use App\Modules\Attendance\Application\ReviewAttendanceJustification;
use App\Modules\Attendance\Domain\Models\AttendanceJustification;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\Attendance\Interfaces\Http\Requests\ReviewAttendanceJustificationRequest;
use App\Modules\Attendance\Interfaces\Http\Requests\StoreAttendanceJustificationRequest;
use App\Modules\Attendance\Interfaces\Http\Resources\AttendanceJustificationResource;
use Illuminate\Http\JsonResponse;

final class AttendanceJustificationController extends Controller
{
    public function store(StoreAttendanceJustificationRequest $request, AttendanceRecord $attendance, RequestAttendanceJustification $requestJustification): JsonResponse
    {
        return AttendanceJustificationResource::make($requestJustification->handle($request->user(), $attendance, $request->validated('reason')))->response()->setStatusCode(201);
    }

    public function review(ReviewAttendanceJustificationRequest $request, AttendanceJustification $justification, ReviewAttendanceJustification $reviewJustification): JsonResponse
    {
        $data = $request->validated();

        return AttendanceJustificationResource::make($reviewJustification->handle($request->user()->id, $justification, $data['status'], $data['reviewer_note'] ?? null))->response();
    }
}
