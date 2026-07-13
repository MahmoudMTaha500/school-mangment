<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['school-admin' => ['school.manage', 'staff.manage', 'sis.manage', 'attendance.record', 'attendance.view', 'homework.manage', 'homework.create', 'homework.grade', 'homework.view', 'wallet.manage', 'wallet.view', 'wallet.topup', 'reports.view'], 'teacher' => ['attendance.record', 'attendance.view', 'homework.create', 'homework.grade', 'homework.view'], 'parent' => ['attendance.view', 'homework.view', 'wallet.view', 'wallet.topup'], 'student' => ['attendance.view', 'homework.view', 'homework.submit', 'wallet.view']] as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            foreach ($permissions as $permission) {
                $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
            }
        }
    }
}
