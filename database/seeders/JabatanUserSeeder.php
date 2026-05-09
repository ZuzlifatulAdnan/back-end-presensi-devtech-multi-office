<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatanUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and positions
        $users = \App\Models\User::all();
        $positions = \App\Models\Jabatan::all();

        if ($users->isEmpty() || $positions->isEmpty()) {
            return;
        }

        // Clear existing pivot data
        DB::table('jabatan_user')->truncate();

        // Assign each user to a position based on their position field
        foreach ($users as $user) {
            // Try to find matching position by name
            $position = $positions->firstWhere('name', $user->position);

            if (! $position) {
                // If no match found, assign to first position
                $position = $positions->first();
            }

            DB::table('jabatan_user')->insert([
                'jabatan_id' => $position->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
