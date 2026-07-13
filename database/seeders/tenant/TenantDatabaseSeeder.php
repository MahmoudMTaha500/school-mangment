<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

final class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesPermissionsSeeder::class, TenantUsersSeeder::class, AcademicYearsSeeder::class, SubjectsSeeder::class, TeachersSeeder::class, ClassSectionsSeeder::class, StudentsSeeder::class, ParentsSeeder::class, TeacherClassSubjectsSeeder::class, AttendanceSeeder::class, HomeworkSeeder::class, WalletSeeder::class, NotificationPreferencesSeeder::class]);
    }
}
