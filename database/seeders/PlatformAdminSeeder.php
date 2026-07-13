<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('PLATFORM_ADMIN_EMAIL');
        $password = env('PLATFORM_ADMIN_PASSWORD');
        if (! $email || ! $password) {
            return;
        }
        User::query()->updateOrCreate(['email' => $email], ['name' => env('PLATFORM_ADMIN_NAME', 'Platform Admin'), 'password' => Hash::make($password), 'is_platform_admin' => true]);
    }
}
