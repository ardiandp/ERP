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
    }
}
