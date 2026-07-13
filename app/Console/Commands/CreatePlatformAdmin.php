<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform:make-admin {email : Platform administrator email} {--name= : Display name} {--password= : A password of at least 12 characters}';

    protected $description = 'Create or promote a central platform administrator';

    public function handle(): int
    {
        $password = (string) $this->option('password');
        if (mb_strlen($password) < 12) {
            $this->error('Supply --password with at least 12 characters.');

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(['email' => $this->argument('email')], ['name' => $this->option('name') ?: $this->argument('email'), 'password' => Hash::make($password), 'is_platform_admin' => true]);
        $this->info("Platform administrator ready: {$user->email}");

        return self::SUCCESS;
    }
}
