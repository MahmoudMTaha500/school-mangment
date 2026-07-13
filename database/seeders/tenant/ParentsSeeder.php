<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Database\Seeder;

final class ParentsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'parent@school.test')->firstOrFail();
        $parent = ParentProfile::query()->firstOrCreate(['user_id' => $user->id]);
        $parent->students()->syncWithoutDetaching([Student::query()->firstOrFail()->id => ['relationship' => 'parent', 'is_primary' => true]]);
    }
}
