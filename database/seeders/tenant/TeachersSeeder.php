<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use App\Modules\Staff\Domain\Models\Teacher;
use Illuminate\Database\Seeder;

final class TeachersSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'teacher@school.test')->firstOrFail();
        Teacher::query()->firstOrCreate(['user_id' => $user->id], ['staff_no' => 'T-001']);
    }
}
