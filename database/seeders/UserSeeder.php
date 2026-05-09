<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\ShiftKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftIds = ShiftKerja::pluck('id', 'name');
        $departemenIds = Departemen::pluck('id', 'name');
        $jabatanIds = Jabatan::pluck('id', 'name');

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'role' => 'admin',
                'position' => 'System Administrator',
                'department' => 'Teknologi Informasi',
                'departemen_id' => $departemenIds['Teknologi Informasi'] ?? null,
                'jabatan_id' => $jabatanIds['Admin'] ?? null,
                'shift_name' => 'Shift Pagi',
                'phone' => '+6281234567890',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@company.com',
                'role' => 'manager',
                'position' => 'Department Manager',
                'department' => 'Human Resources',
                'departemen_id' => $departemenIds['Human Resources'] ?? null,
                'jabatan_id' => $jabatanIds['HR Manager'] ?? null,
                'shift_name' => 'Shift Pagi',
                'phone' => '+6281234567891',
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@company.com',
                'role' => 'employee',
                'position' => 'Senior Developer',
                'department' => 'Teknologi Informasi',
                'departemen_id' => $departemenIds['Teknologi Informasi'] ?? null,
                'jabatan_id' => $jabatanIds['Senior Developer'] ?? null,
                'shift_name' => 'Shift Pagi',
                'phone' => '+6281234567892',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@company.com',
                'role' => 'employee',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'departemen_id' => $departemenIds['Marketing'] ?? null,
                'jabatan_id' => $jabatanIds['Marketing Manager'] ?? null,
                'shift_name' => 'Shift Flexible',
                'phone' => '+6281234567893',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@company.com',
                'role' => 'employee',
                'position' => 'Sales Executive',
                'department' => 'Sales',
                'departemen_id' => $departemenIds['Sales'] ?? null,
                'jabatan_id' => $jabatanIds['Sales Executive'] ?? null,
                'shift_name' => 'Shift Siang',
                'phone' => '+6281234567894',
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'siti@company.com',
                'role' => 'employee',
                'position' => 'Finance Analyst',
                'department' => 'Finance',
                'departemen_id' => $departemenIds['Finance'] ?? null,
                'jabatan_id' => $jabatanIds['Project Manager'] ?? null,
                'shift_name' => 'Shift Pagi',
                'phone' => '+6281234567895',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@company.com',
                'role' => 'employee',
                'position' => 'Operations Supervisor',
                'department' => 'Operations',
                'departemen_id' => $departemenIds['Operations'] ?? null,
                'jabatan_id' => $jabatanIds['Project Manager'] ?? null,
                'shift_name' => 'Shift Malam',
                'phone' => '+6281234567896',
            ],
            [
                'name' => 'Maria Clara',
                'email' => 'maria@company.com',
                'role' => 'employee',
                'position' => 'HR Specialist',
                'department' => 'Human Resources',
                'departemen_id' => $departemenIds['Human Resources'] ?? null,
                'jabatan_id' => $jabatanIds['HR Manager'] ?? null,
                'shift_name' => 'Shift Flexible',
                'phone' => '+6281234567897',
            ],
        ];

        foreach ($users as $userData) {
            $shiftId = $shiftIds->get($userData['shift_name']) ?? $shiftIds->first();

            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'phone' => $userData['phone'],
                    'role' => $userData['role'],
                    'position' => $userData['position'],
                    'department' => $userData['department'],
                    'departemen_id' => $userData['departemen_id'],
                    'jabatan_id' => $userData['jabatan_id'],
                    'shift_kerja_id' => $shiftId,
                ]
            );
        }
    }
}
