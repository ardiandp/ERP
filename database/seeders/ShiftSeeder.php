<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        $shifts = [
            ['code' => 'PAGI', 'name' => 'Shift Pagi', 'start_time' => '08:00', 'end_time' => '16:00', 'break_minutes' => 60],
            ['code' => 'SIANG', 'name' => 'Shift Siang', 'start_time' => '16:00', 'end_time' => '23:00', 'break_minutes' => 60],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['code' => $shift['code']],
                [
                    'branch_id' => $branch?->id,
                    'name' => $shift['name'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'break_minutes' => $shift['break_minutes'],
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
}
