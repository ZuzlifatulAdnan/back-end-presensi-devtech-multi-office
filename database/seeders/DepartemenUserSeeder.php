<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartemenUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and departments
        $users = \App\Models\User::all();
        $departments = \App\Models\Departemen::all();

        if ($users->isEmpty() || $departments->isEmpty()) {
            return;
        }

        // Clear existing pivot data
        DB::table('departemen_user')->truncate();

        // Assign each user to a department based on their department field
        foreach ($users as $user) {
            // Try to find matching department by name
            $department = $departments->firstWhere('name', $user->department);

            if (! $department) {
                // If no match found, assign to first department
                $department = $departments->first();
            }

            DB::table('departemen_user')->insert([
                'departemen_id' => $department->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
