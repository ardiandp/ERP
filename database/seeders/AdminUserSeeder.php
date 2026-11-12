<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'branch-manager')->first() ?? $adminRole;
        $staffRole = Role::where('name', 'staff')->first() ?? $adminRole;

        $owner = User::updateOrCreate(
            ['email' => 'owner@dianmustika.test'],
            ['name' => 'Dian Mustika (Owner)', 'password' => Hash::make('password'), 'status' => 'ACTIVE']
        );
        if ($ownerRole) {
            $owner->roles()->syncWithoutDetaching([$ownerRole->id]);
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@dianmustika.test'],
            ['name' => 'System Administrator', 'password' => Hash::make('password'), 'status' => 'ACTIVE']
        );
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
        if ($ownerRole) {
            $admin->roles()->syncWithoutDetaching([$ownerRole->id]);
        }

        $demoUsers = [
            ['email' => 'manager.cabang@dianmustika.test', 'name' => 'Manager Cabang', 'role' => $managerRole],
            ['email' => 'kasir@dianmustika.test', 'name' => 'Kasir Dian Mustika', 'role' => $staffRole],
            ['email' => 'terapis@dianmustika.test', 'name' => 'Terapis Dian Mustika', 'role' => $staffRole],
            ['email' => 'finance@dianmustika.test', 'name' => 'Finance & Accounting', 'role' => $staffRole],
            ['email' => 'hrd@dianmustika.test', 'name' => 'HR & Payroll Staff', 'role' => $staffRole],
        ];

        foreach ($demoUsers as $du) {
            $u = User::updateOrCreate(
                ['email' => $du['email']],
                ['name' => $du['name'], 'password' => Hash::make('password'), 'status' => 'ACTIVE']
            );
            if ($du['role']) {
                $u->roles()->syncWithoutDetaching([$du['role']->id]);
            }
        }
    }
}
