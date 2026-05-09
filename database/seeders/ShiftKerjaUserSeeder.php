<?php

namespace Database\Seeders;

use App\Models\ShiftKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftKerjaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $shifts = ShiftKerja::all();

        if ($users->isEmpty() || $shifts->isEmpty()) {
            return;
        }

        DB::table('shift_kerja_user')->truncate();

        foreach ($users as $user) {
            $assignedShift = $user->shiftKerja
                ?? $shifts->firstWhere('id', $user->shift_kerja_id)
                ?? ($user->role === 'manager' ? $shifts->last() : $shifts->first());

            if (! $assignedShift) {
                continue;
            }

            if (! $user->shift_kerja_id) {
                $user->update(['shift_kerja_id' => $assignedShift->id]);
            }

            DB::table('shift_kerja_user')->insert([
                'shift_kerja_id' => $assignedShift->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
