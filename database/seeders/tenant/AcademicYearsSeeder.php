<?php

namespace Database\Seeders\Tenant;

use App\Modules\SIS\Domain\Models\AcademicYear;
use Illuminate\Database\Seeder;

final class AcademicYearsSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::query()->firstOrCreate(['name' => '2026/2027'], ['starts_on' => '2026-09-01', 'ends_on' => '2027-06-30']);
    }
}
