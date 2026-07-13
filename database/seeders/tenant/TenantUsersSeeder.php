<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class TenantUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['School Admin', 'admin@school.test', 'school-admin'], ['Teacher One', 'teacher@school.test', 'teacher'], ['Parent One', 'parent@school.test', 'parent'], ['Student One', 'student@school.test', 'student']] as [$name, $email, $role]) {
            $user = User::query()->firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
            $user->assignRole($role);
        }
    }
}
