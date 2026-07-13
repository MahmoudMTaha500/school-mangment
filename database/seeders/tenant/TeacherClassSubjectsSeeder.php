<?php

namespace Database\Seeders\Tenant;

use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\Staff\Domain\Models\Subject;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TeacherClassSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('teacher_class_subject')->insertOrIgnore(['teacher_id' => Teacher::query()->firstOrFail()->id, 'class_section_id' => ClassSection::query()->firstOrFail()->id, 'subject_id' => Subject::query()->firstOrFail()->id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
