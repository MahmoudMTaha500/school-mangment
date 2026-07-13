<?php

namespace Database\Seeders\Tenant;

use App\Modules\SIS\Domain\Models\AcademicYear;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Database\Seeder;

final class ClassSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()->firstOrFail();
        $teacher = Teacher::query()->firstOrFail();
        ClassSection::query()->firstOrCreate(['academic_year_id' => $year->id, 'grade' => 'Grade 5', 'section' => 'A'], ['homeroom_teacher_id' => $teacher->id]);
    }
}
