<?php

namespace App\Modules\Tenancy\Application;

use App\Models\User;
use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ProvisionSchool
{
    private const ROLE_PERMISSIONS = [
        'school-admin' => ['school.manage', 'staff.manage', 'sis.manage', 'attendance.record', 'attendance.view', 'homework.manage', 'homework.create', 'homework.grade', 'homework.view', 'wallet.manage', 'wallet.view', 'wallet.topup', 'reports.view'],
        'teacher' => ['attendance.record', 'attendance.view', 'homework.create', 'homework.grade', 'homework.view'],
        'parent' => ['attendance.view', 'homework.view', 'wallet.view', 'wallet.topup'],
        'student' => ['attendance.view', 'homework.view', 'homework.submit', 'wallet.view'],
    ];

    /** @param array{name:string,slug:string,domain:string,timezone:string,locale:string,admin_name:string,admin_email:string,admin_password:string} $data */
    public function handle(array $data): SchoolTenant
    {
        $school = SchoolTenant::create([
            'id' => $data['slug'],
            'name' => $data['name'],
            'timezone' => $data['timezone'],
            'locale' => $data['locale'],
            'subscription_plan' => 'trial',
            'status' => 'active',
        ]);
        $school->domains()->create(['domain' => $data['domain']]);

        $school->run(function () use ($data): void {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $roles = [];

            foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
                $roles[$roleName] = Role::findOrCreate($roleName, 'web');
                foreach ($permissions as $permissionName) {
                    $roles[$roleName]->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
                }
            }

            $admin = User::query()->create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
            ]);
            $admin->assignRole($roles['school-admin']);
        });

        return $school;
    }
}
