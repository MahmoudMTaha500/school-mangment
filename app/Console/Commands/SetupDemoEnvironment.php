<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Tenancy\Application\ProvisionSchool;
use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use Database\Seeders\Tenant\TenantDatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

final class SetupDemoEnvironment extends Command
{
    protected $signature = 'demo:setup {--force : Allow the idempotent demo setup to run}';

    protected $description = 'Migrate and seed the review environment with predictable demo accounts';

    public function handle(ProvisionSchool $provisionSchool): int
    {
        if (app()->isProduction() && (! $this->option('force') || ! env('DEMO_SETUP_ENABLED', false))) {
            $this->error('Demo setup is disabled in production.');

            return self::FAILURE;
        }

        if (! config('app.key')) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('migrate', ['--force' => true]);
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Platform Admin', 'password' => Hash::make('password'), 'is_platform_admin' => true],
        );

        $tenant = SchoolTenant::query()->find('green-valley');
        if (! $tenant) {
            $tenant = $provisionSchool->handle([
                'name' => 'Green Valley School',
                'slug' => 'green-valley',
                'domain' => 'green-valley.localhost',
                'timezone' => 'Africa/Cairo',
                'locale' => 'en',
                'admin_name' => 'School Admin',
                'admin_email' => 'school-admin@example.com',
                'admin_password' => 'password',
            ]);
        } elseif (! $tenant->domains()->where('domain', 'green-valley.localhost')->exists()) {
            $tenant->domains()->create(['domain' => 'green-valley.localhost']);
        }

        Artisan::call('tenants:migrate', ['--tenants' => [$tenant->id], '--force' => true]);
        $tenant->run(function (): void {
            Artisan::call('db:seed', ['--class' => TenantDatabaseSeeder::class, '--force' => true]);
            $admin = User::query()->updateOrCreate(
                ['email' => 'school-admin@example.com'],
                ['name' => 'School Admin', 'password' => Hash::make('password')],
            );
            $admin->assignRole('school-admin');
        });

        $this->components->info('Demo environment ready.');
        $this->line('Platform: admin@example.com / password');
        $this->line('School: school-admin@example.com / password');
        $this->line('Tenant host: green-valley.localhost');

        return self::SUCCESS;
    }
}
