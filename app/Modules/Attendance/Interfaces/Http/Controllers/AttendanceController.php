<?php

namespace App\Modules\Attendance\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Application\CorrectAttendance;
use App\Modules\Attendance\Application\RecordAttendance;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\Attendance\Interfaces\Http\Requests\CorrectAttendanceRequest;
use App\Modules\Attendance\Interfaces\Http\Requests\StoreAttendanceRequest;
use Illuminate\Http\JsonResponse;

final class AttendanceController extends Controller
{
    public function correct(CorrectAttendanceRequest $request, AttendanceRecord $attendance, CorrectAttendance $correctAttendance): JsonResponse
    {
        $data = $request->validated();

        return response()->json(['data' => $correctAttendance->handle($request->user()->id, $attendance, $data['status'], $data['reason'])]);
    }

    public function store(StoreAttendanceRequest $request, RecordAttendance $recordAttendance): JsonResponse
    {
        $data = $request->validated();
        $data['period'] ??= 0;
        $recordAttendance->handle($request->user()->id, $data);

        return response()->json(status: 204);
    }
}
