<?php

namespace Database\Seeders\Tenant;

use App\Modules\Staff\Domain\Models\Subject;
use Illuminate\Database\Seeder;

final class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Mathematics', 'English', 'Science'] as $name) {
            Subject::query()->firstOrCreate(['name' => $name]);
        }
    }
}
