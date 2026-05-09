<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            ['name' => 'Direktur Utama', 'description' => 'Pemimpin tertinggi perusahaan'],
            ['name' => 'Manajer IT', 'description' => 'Mengelola departemen teknologi informasi'],
            ['name' => 'Senior Developer', 'description' => 'Pengembang senior aplikasi'],
            ['name' => 'Junior Developer', 'description' => 'Pengembang junior aplikasi'],
            ['name' => 'UI/UX Designer', 'description' => 'Desainer antarmuka dan pengalaman pengguna'],
            ['name' => 'Project Manager', 'description' => 'Mengelola proyek-proyek perusahaan'],
            ['name' => 'HR Manager', 'description' => 'Mengelola sumber daya manusia'],
            ['name' => 'Marketing Manager', 'description' => 'Mengelola strategi pemasaran'],
            ['name' => 'Sales Executive', 'description' => 'Eksekutif penjualan'],
            ['name' => 'Admin', 'description' => 'Administrator sistem'],
        ];

        foreach ($jabatans as $jabatan) {
            \App\Models\Jabatan::updateOrCreate(
                ['name' => $jabatan['name']],
                $jabatan
            );
        }

        $this->command->info('10 jabatans created/updated successfully.');
    }
}
