<?php

namespace App\Modules\Reporting\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Application\BuildAttendanceSummary;
use App\Modules\Reporting\Application\BuildHomeworkSummary;
use App\Modules\Reporting\Application\BuildWalletSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController extends Controller
{
    public function attendance(Request $request, BuildAttendanceSummary $buildAttendanceSummary): JsonResponse
    {
        $data = $request->validate(['class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'from' => ['required', 'date'], 'to' => ['required', 'date', 'after_or_equal:from']]);

        return response()->json(['data' => $buildAttendanceSummary->handle($data['class_section_id'], $data['from'], $data['to'])]);
    }

    public function wallet(Request $request, BuildWalletSummary $buildWalletSummary): JsonResponse
    {
        $data = $request->validate(['from' => ['required', 'date'], 'to' => ['required', 'date', 'after_or_equal:from']]);

        return response()->json(['data' => $buildWalletSummary->handle($data['from'], $data['to'])]);
    }

    public function homework(Request $request, BuildHomeworkSummary $buildHomeworkSummary): JsonResponse
    {
        $data = $request->validate(['class_section_id' => ['required', 'integer', 'exists:class_sections,id']]);

        return response()->json(['data' => $buildHomeworkSummary->handle($data['class_section_id'])]);
    }

    public function attendanceCsv(Request $request, BuildAttendanceSummary $buildAttendanceSummary): StreamedResponse
    {
        $data = $request->validate(['class_section_id' => ['required', 'integer', 'exists:class_sections,id'], 'from' => ['required', 'date'], 'to' => ['required', 'date', 'after_or_equal:from']]);

        return response()->streamDownload(function () use ($buildAttendanceSummary, $data): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['status', 'total']);
            foreach ($buildAttendanceSummary->handle($data['class_section_id'], $data['from'], $data['to']) as $row) {
                fputcsv($stream, $row);
            } fclose($stream);
        }, 'attendance-summary.csv', ['Content-Type' => 'text/csv']);
    }
}
