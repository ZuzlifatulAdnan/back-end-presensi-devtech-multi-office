<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departemens = [
            ['name' => 'Teknologi Informasi', 'description' => 'Departemen yang mengelola teknologi dan sistem informasi'],
            ['name' => 'Human Resources', 'description' => 'Departemen yang mengelola sumber daya manusia'],
            ['name' => 'Marketing', 'description' => 'Departemen yang mengelola pemasaran dan promosi'],
            ['name' => 'Sales', 'description' => 'Departemen yang mengelola penjualan'],
            ['name' => 'Finance', 'description' => 'Departemen yang mengelola keuangan'],
            ['name' => 'Operations', 'description' => 'Departemen yang mengelola operasional harian'],
            ['name' => 'Administration', 'description' => 'Departemen yang mengelola administrasi umum'],
        ];

        foreach ($departemens as $departemen) {
            \App\Models\Departemen::updateOrCreate(
                ['name' => $departemen['name']],
                $departemen
            );
        }

        $this->command->info('7 departemens created/updated successfully.');
    }
}
