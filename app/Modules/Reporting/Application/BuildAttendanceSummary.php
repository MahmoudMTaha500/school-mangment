<?php

namespace App\Modules\Reporting\Application;

use Illuminate\Support\Facades\DB;

final class BuildAttendanceSummary
{
    /** @return array<int, array{status:string,total:int}> */
    public function handle(int $classSectionId, string $from, string $to): array
    {
        return DB::table('attendance')->select('status', DB::raw('COUNT(*) as total'))->where('class_section_id', $classSectionId)->whereBetween('date', [$from, $to])->groupBy('status')->orderBy('status')->get()->map(fn ($row) => ['status' => $row->status, 'total' => (int) $row->total])->all();
    }
}
