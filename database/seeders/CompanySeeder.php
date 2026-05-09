<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Company::updateOrCreate(
            ['email' => 'info@abctech.com'],
            [
                'name' => 'PT. ABC Technology',
                'email' => 'info@abctech.com',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta',
                'latitude' => '-6.208763',
                'longitude' => '106.845599',
                'radius_km' => '0.5',
                'attendance_type' => 'location_based',
            ]
        );
    }
}
