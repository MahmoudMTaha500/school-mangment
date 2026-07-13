<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use App\Modules\SIS\Domain\Models\ClassSection;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Database\Seeder;

final class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $class = ClassSection::query()->firstOrFail();
        $user = User::query()->where('email', 'student@school.test')->firstOrFail();
        Student::query()->firstOrCreate(['code' => 'S-001'], ['user_id' => $user->id, 'class_section_id' => $class->id, 'first_name' => 'Student', 'last_name' => 'One', 'dob' => '2015-01-01']);
    }
}
