<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use App\Modules\Attendance\Domain\Models\AttendanceRecord;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Database\Seeder;

final class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::query()->firstOrFail();
        AttendanceRecord::query()->updateOrCreate(['student_id' => $student->id, 'date' => today()->toDateString(), 'period' => 0], ['class_section_id' => ClassSection::query()->firstOrFail()->id, 'status' => 'present', 'recorded_by' => User::query()->where('email', 'teacher@school.test')->value('id')]);
    }
}
