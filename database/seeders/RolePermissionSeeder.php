<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'company.view', 'company.manage',
            'branch.view', 'branch.manage',
            'user.view', 'user.manage',
            'role.view', 'role.manage',
            'permission.manage',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name], ['guard_name' => 'web']);
        }

        $owner = Role::firstOrCreate(['name' => 'owner'], ['guard_name' => 'web', 'description' => 'Owner / Super Admin']);
        $owner->permissions()->sync(Permission::all()->pluck('id'));

        $admin = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web', 'description' => 'Administrator']);
        $admin->permissions()->sync(Permission::whereIn('name', ['dashboard.view', 'company.view', 'company.manage', 'branch.view', 'branch.manage', 'user.view', 'user.manage', 'role.view', 'settings.manage', 'audit.view'])->pluck('id'));

        Role::firstOrCreate(['name' => 'branch-manager'], ['guard_name' => 'web', 'description' => 'Branch Manager']);
        Role::firstOrCreate(['name' => 'staff'], ['guard_name' => 'web', 'description' => 'Staff']);
    }
}
