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
        $user = User::updateOrCreate(
            ['email' => 'admin@dianmustika.test'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'status' => 'ACTIVE']
        );

        $owner = Role::where('name', 'owner')->first();
        if ($owner) {
            $user->roles()->syncWithoutDetaching([$owner->id]);
        }
    }
}
